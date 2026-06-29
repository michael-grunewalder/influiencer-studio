<?php

namespace App\Services;

use App\Data\InfluencerProperties;
use App\Models\Influencer;
use App\Models\Outfit;
use App\Models\TeamAsset;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class InfluencerImportExportService
{
    /**
     * Export an influencer to a temporary .isdata (zip) file path.
     *
     * @throws \RuntimeException
     */
    public function export(Influencer $influencer): string
    {
        if (! class_exists('ZipArchive')) {
            throw new \RuntimeException('ZipArchive PHP extension is not installed.');
        }

        $teamId = $influencer->team_id;
        $influencerId = $influencer->id;

        Log::info("Starting export for influencer {$influencer->name} ({$influencerId})");

        // Query related outfits
        $outfits = Outfit::where('influencer_id', $influencerId)->get();

        // Query related team assets (by path containment or purpose containing ID)
        $assets = TeamAsset::where('team_id', $teamId)
            ->where(function ($query) use ($influencerId) {
                $query->where('local_url', 'like', "%/influencers/{$influencerId}/%")
                    ->orWhere('purpose', 'like', "%{$influencerId}%");
            })
            ->get();

        // Strip signatures from raw paths
        $avatarPath = $influencer->getRawOriginal('avatar');
        $propertiesData = $influencer->getRawOriginal('properties');
        $properties = is_string($propertiesData) ? json_decode($propertiesData, true) : ($influencer->properties ? $influencer->properties->toArray() : []);

        $avatarClean = $this->stripSignature($avatarPath);

        // Sanitize properties URLs
        $propertiesClean = $this->sanitizeArrayUrls($properties);

        $cleanOutfits = [];
        foreach ($outfits as $outfit) {
            $cleanOutfits[] = [
                'id' => $outfit->id,
                'influencer_id' => $outfit->influencer_id,
                'name' => $outfit->name,
                'top' => $outfit->top,
                'bottom' => $outfit->bottom,
                'hairstyle' => $outfit->hairstyle,
                'full_look_description' => $outfit->full_look_description,
                'image_path' => $this->stripSignature($outfit->getRawOriginal('image_path')),
                'created_at' => $outfit->created_at?->toDateTimeString(),
                'updated_at' => $outfit->updated_at?->toDateTimeString(),
            ];
        }

        $cleanAssets = [];
        foreach ($assets as $asset) {
            $cleanAssets[] = [
                'id' => $asset->id,
                'team_id' => $asset->team_id,
                'local_url' => $this->stripSignature($asset->local_url),
                'remote_url' => $asset->remote_url,
                'mime_type' => $asset->mime_type,
                'purpose' => $asset->purpose,
                'meta_data' => $asset->meta_data,
                'created_at' => $asset->created_at?->toDateTimeString(),
                'updated_at' => $asset->updated_at?->toDateTimeString(),
            ];
        }

        $metadata = [
            'version' => '1.0',
            'influencer' => [
                'id' => $influencerId,
                'team_id' => $teamId,
                'name' => $influencer->name,
                'stagename' => $influencer->stagename,
                'avatar' => $avatarClean,
                'bio' => $influencer->bio,
                'properties' => $propertiesClean,
            ],
            'outfits' => $cleanOutfits,
            'assets' => $cleanAssets,
        ];

        // Create temporary ZIP archive
        $tempDir = storage_path('app/private/temp');
        if (! File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true, true);
        }

        $zipFileName = 'export_'.$influencerId.'_'.time().'.isdata';
        $zipFilePath = $tempDir.'/'.$zipFileName;

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Could not create ZIP archive at: {$zipFilePath}");
        }

        // Add metadata JSON
        $zip->addFromString('influencer.json', json_encode($metadata, JSON_PRETTY_PRINT));

        // Gather all files from the local influencer folder
        $baseDir = "teams/{$teamId}/influencers/{$influencerId}";
        $files = Storage::disk('local')->allFiles($baseDir);

        foreach ($files as $file) {
            $relativePath = ltrim(substr($file, strlen($baseDir)), '/');
            $filePathOnDisk = Storage::disk('local')->path($file);
            if (file_exists($filePathOnDisk)) {
                $zip->addFile($filePathOnDisk, $relativePath);
            }
        }

        $zip->close();

        Log::info("Export completed successfully. File: {$zipFilePath}");

        return $zipFilePath;
    }

    /**
     * Import an influencer from an .isdata zip archive.
     *
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function import(string $zipFilePath, string $targetTeamId): Influencer
    {
        if (! class_exists('ZipArchive')) {
            throw new \RuntimeException('ZipArchive PHP extension is not installed.');
        }

        if (! file_exists($zipFilePath)) {
            throw new \InvalidArgumentException("Export archive file does not exist at: {$zipFilePath}");
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath) !== true) {
            throw new \RuntimeException("Failed to open ZIP archive at: {$zipFilePath}");
        }

        $metadataString = $zip->getFromName('influencer.json');
        if ($metadataString === false) {
            $zip->close();
            throw new \RuntimeException('Invalid archive: influencer.json not found.');
        }

        $metadata = json_decode($metadataString, true);
        if (! is_array($metadata) || ! isset($metadata['influencer'])) {
            $zip->close();
            throw new \RuntimeException('Invalid archive metadata in influencer.json.');
        }

        $oldInfluencer = $metadata['influencer'];
        $oldInfluencerId = $oldInfluencer['id'];
        $oldTeamId = $oldInfluencer['team_id'] ?? null;

        // Check if influencer ULID already exists in the system
        $exists = Influencer::where('id', $oldInfluencerId)->exists();
        if ($exists) {
            // Generate a new ULID
            $newInfluencerId = (string) Str::ulid();
            Log::info("ULID collision detected for influencer {$oldInfluencerId}. Generated new ULID: {$newInfluencerId}");
        } else {
            // Keep the old ULID
            $newInfluencerId = $oldInfluencerId;
            Log::info("Importing influencer using original ULID: {$newInfluencerId}");
        }

        $newTeamId = $targetTeamId;

        // Define mapping search and replacements
        $oldPathToken = "/storage/teams/{$oldTeamId}/influencers/{$oldInfluencerId}/";
        $newPathToken = "/storage/teams/{$newTeamId}/influencers/{$newInfluencerId}/";

        // Extract files from ZIP to the local disk at the correct destination
        $destFolder = "teams/{$newTeamId}/influencers/{$newInfluencerId}";

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileName = $zip->getNameIndex($i);
            if ($fileName === 'influencer.json' || str_ends_with($fileName, '/') || str_contains($fileName, '..')) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                $targetFile = $destFolder.'/'.$fileName;
                Storage::disk('local')->put($targetFile, $content);
            }
        }
        $zip->close();

        // Perform recursive placeholder replacement for paths and IDs
        $metadata = $this->replacePlaceholders($metadata, $oldInfluencerId, $newInfluencerId, $oldPathToken, $newPathToken, $oldTeamId, $newTeamId);

        $cleanInfluencerData = $metadata['influencer'];
        $cleanInfluencerData['id'] = $newInfluencerId;
        $cleanInfluencerData['team_id'] = $newTeamId;

        $propData = $cleanInfluencerData['properties'] ?? [];
        $properties = new InfluencerProperties(
            gender: $propData['gender'] ?? null,
            age: isset($propData['age']) ? (int) $propData['age'] : null,
            niche: $propData['niche'] ?? [],
            backstory: $propData['backstory'] ?? null,
            personality: isset($propData['personality']) ? (int) $propData['personality'] : 50,
            face_reference: $propData['face_reference'] ?? null,
            style_reference: $propData['style_reference'] ?? null,
            ethnicity: $propData['ethnicity'] ?? null,
            skin_tone: $propData['skin_tone'] ?? null,
            hair_color: $propData['hair_color'] ?? null,
            hair_length: $propData['hair_length'] ?? null,
            hair_texture: $propData['hair_texture'] ?? null,
            eye_color: $propData['eye_color'] ?? null,
            build: $propData['build'] ?? null,
            custom_description: $propData['custom_description'] ?? null,
            aesthetic_vibe: $propData['aesthetic_vibe'] ?? null,
            character_sheet: $propData['character_sheet'] ?? null,
            closeup: $propData['closeup'] ?? null,
            detail_sheet: $propData['detail_sheet'] ?? null,
            location: $propData['location'] ?? null,
            target_audience: $propData['target_audience'] ?? null,
            physical_description: $propData['physical_description'] ?? null,
        );

        // Create the influencer in DB
        $influencer = Influencer::create([
            'id' => $cleanInfluencerData['id'],
            'team_id' => $cleanInfluencerData['team_id'],
            'name' => $cleanInfluencerData['name'] ?? null,
            'stagename' => $cleanInfluencerData['stagename'] ?? null,
            'avatar' => $cleanInfluencerData['avatar'] ?? null,
            'bio' => $cleanInfluencerData['bio'] ?? null,
            'properties' => $properties,
        ]);

        // Import Outfits
        $outfits = $metadata['outfits'] ?? [];
        foreach ($outfits as $outfitData) {
            Outfit::create([
                'id' => (string) Str::ulid(), // generate new ULID for outfits
                'influencer_id' => $newInfluencerId,
                'name' => $outfitData['name'] ?? null,
                'top' => $outfitData['top'] ?? null,
                'bottom' => $outfitData['bottom'] ?? null,
                'hairstyle' => $outfitData['hairstyle'] ?? null,
                'full_look_description' => $outfitData['full_look_description'] ?? null,
                'image_path' => $outfitData['image_path'] ?? null,
            ]);
        }

        // Import TeamAssets
        $assets = $metadata['assets'] ?? [];
        foreach ($assets as $assetData) {
            TeamAsset::create([
                'id' => (string) Str::ulid(), // generate new ULID for assets
                'team_id' => $newTeamId,
                'local_url' => $assetData['local_url'],
                'remote_url' => $assetData['remote_url'] ?? null,
                'mime_type' => $assetData['mime_type'] ?? null,
                'purpose' => $assetData['purpose'] ?? null,
                'meta_data' => $assetData['meta_data'] ?? null,
            ]);
        }

        Log::info("Influencer {$newInfluencerId} imported successfully to team {$newTeamId}");

        return $influencer;
    }

    /**
     * Import an influencer from a downloadable url.
     *
     * @throws \RuntimeException
     */
    public function importFromUrl(string $url, string $targetTeamId): Influencer
    {
        Log::info("Downloading export file from link: {$url}");

        $response = Http::get($url);
        if ($response->failed()) {
            throw new \RuntimeException("Failed to download export file from: {$url}");
        }

        $tempDir = storage_path('app/private/temp');
        if (! File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true, true);
        }

        $tempFilePath = $tempDir.'/download_'.uniqid().'.isdata';
        file_put_contents($tempFilePath, $response->body());

        try {
            $influencer = $this->import($tempFilePath, $targetTeamId);
        } finally {
            if (file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }
        }

        return $influencer;
    }

    /**
     * Strip temporary URL signature parameters from the path.
     */
    protected function stripSignature(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $pos = strpos($url, '/storage/teams/');
        if ($pos !== false) {
            $clean = substr($url, $pos);

            return explode('?', $clean)[0];
        }

        return $url;
    }

    /**
     * Sanitize urls recursively within an array.
     */
    protected function sanitizeArrayUrls(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeArrayUrls($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->stripSignature($value);
            }
        }

        return $data;
    }

    /**
     * Recursively replace old paths and IDs.
     */
    protected function replacePlaceholders(
        mixed $data,
        string $oldInfluencerId,
        string $newInfluencerId,
        string $oldPath,
        string $newPath,
        ?string $oldTeamId,
        string $newTeamId
    ): mixed {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replacePlaceholders($value, $oldInfluencerId, $newInfluencerId, $oldPath, $newPath, $oldTeamId, $newTeamId);
            }

            return $data;
        }

        if (is_string($data)) {
            $data = str_replace($oldPath, $newPath, $data);
            $data = str_replace($oldInfluencerId, $newInfluencerId, $data);
            if ($oldTeamId) {
                $data = str_replace($oldTeamId, $newTeamId, $data);
            }

            return $data;
        }

        return $data;
    }
}
