<?php

namespace App\Services;

use App\Data\InfluencerProperties;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PromptBuilderService
{
    /**
     * Build the physical description string from discrete properties.
     */
    public static function buildPhysicalDescString(array|object $data): string
    {
        $d = (array) $data;

        $ethnicity = $d['ethnicity'] ?? null;
        $skinTone = $d['skin_tone'] ?? $d['skinTone'] ?? null;
        $hairColor = $d['hair_color'] ?? $d['hairColor'] ?? null;
        $hairLength = $d['hair_length'] ?? $d['hairLength'] ?? null;
        $hairTexture = $d['hair_texture'] ?? $d['hairTexture'] ?? null;
        $eyeColor = $d['eye_color'] ?? $d['eyeColor'] ?? null;
        $build = $d['build'] ?? null;
        $uniqueFeatures = $d['unique_features'] ?? $d['uniqueFeatures'] ?? $d['custom_description'] ?? $d['customDescription'] ?? null;

        $parts = [];
        if ($ethnicity) {
            $parts[] = strtolower($ethnicity);
        }

        $hairParts = array_filter(array_map('strtolower', array_filter([$hairLength, $hairTexture, $hairColor])));
        if (! empty($hairParts)) {
            $parts[] = implode(' ', $hairParts).' hair';
        }

        if ($eyeColor) {
            $parts[] = strtolower($eyeColor).' eyes';
        }

        if ($skinTone) {
            $parts[] = strtolower($skinTone).' skin tone';
        }

        if ($build) {
            $parts[] = strtolower($build).' build';
        }

        if ($uniqueFeatures && trim($uniqueFeatures) !== '') {
            $parts[] = trim($uniqueFeatures);
        }

        return implode(', ', $parts);
    }

    /**
     * Get the backstory context matching archetypes from the configuration.
     */
    public static function getBackstoryContext(?string $physicalDesc, ?string $backstory): array
    {
        $text = strtolower(($physicalDesc ?? '').' '.($backstory ?? ''));
        $tags = [];
        $sceneNiche = null;
        $buildHint = null;
        $physicalDetail = null;
        $lockedScene = null;

        $archetypes = config('prompts.BACKSTORY_ARCHETYPES', []);
        foreach ($archetypes as $archetype) {
            if (isset($archetype['test']) && preg_match($archetype['test'], $text)) {
                if (isset($archetype['tags'])) {
                    foreach ($archetype['tags'] as $tag) {
                        if (! in_array($tag, $tags, true)) {
                            $tags[] = $tag;
                        }
                    }
                }
                if (! $sceneNiche && ! empty($archetype['sceneNiche'])) {
                    $sceneNiche = $archetype['sceneNiche'];
                }
                if (! $buildHint && ! empty($archetype['buildHint'])) {
                    $buildHint = $archetype['buildHint'];
                }
                if (! $physicalDetail && ! empty($archetype['physicalDetail'])) {
                    $physicalDetail = $archetype['physicalDetail'];
                }
                if (! $lockedScene && ! empty($archetype['lockedScene'])) {
                    $lockedScene = $archetype['lockedScene'];
                }
            }
        }

        return [
            'tags' => $tags,
            'sceneNiche' => $sceneNiche,
            'buildHint' => $buildHint,
            'physicalDetail' => $physicalDetail,
            'lockedScene' => $lockedScene,
        ];
    }

    /**
     * Select a wardrobe description from the library based on the vibe, personality and gender.
     */
    public static function selectWardrobe(
        ?string $gender,
        array $vibeWords,
        int $personality,
        ?string $physicalDesc,
        ?string $backstory,
        ?array $forceProfessionTags = null
    ): string {
        $isMale = strtolower($gender ?? '') === 'male';

        $backstoryCtx = self::getBackstoryContext($physicalDesc, $backstory);
        $vibeTags = array_merge(self::getVibeTags($vibeWords), $backstoryCtx['tags']);

        $vibePrimaryTag = config('prompts.VIBE_PRIMARY_TAG', []);
        $primaryVibeTags = $forceProfessionTags;
        if ($primaryVibeTags === null) {
            $primaryVibeTags = [];
            foreach ($vibeWords as $v) {
                if (isset($vibePrimaryTag[$v])) {
                    $primaryVibeTags[] = $vibePrimaryTag[$v];
                }
            }
        }

        $wardrobes = config('prompts.WARDROBE', []);
        $genderPool = array_filter($wardrobes, function ($e) use ($isMale) {
            return $isMale ? ($e['gender'] === 'male') : ($e['gender'] === 'female');
        });

        if (! empty($primaryVibeTags)) {
            $filtered = array_filter($genderPool, function ($e) use ($primaryVibeTags) {
                foreach ($e['tags'] as $t) {
                    if (in_array($t, $primaryVibeTags, true)) {
                        return true;
                    }
                }

                return false;
            });
            $pool = count($filtered) >= 3 ? $filtered : $genderPool;
        } else {
            $pool = $genderPool;
        }

        $scored = [];
        foreach ($pool as $e) {
            $score = 0;

            $energyDist = abs($e['energy'] - $personality);
            if ($energyDist <= 15) {
                $score += 40;
            } elseif ($energyDist <= 28) {
                $score += 20;
            } else {
                $score -= 20;
            }

            if (! empty($vibeTags)) {
                $tagMatches = count(array_intersect($e['tags'], $vibeTags));
                $score += $tagMatches * 18;
            }

            $score += (mt_rand(0, 1000) / 1000) * 14;

            $e['score'] = $score;
            $scored[] = $e;
        }

        usort($scored, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $topN = min(6, count($scored));
        $chosen = array_slice($scored, 0, $topN);
        $entry = self::randomElement($chosen);

        return ($entry['text'] ?? '').'. '.self::getStylingNote($personality);
    }

    /**
     * Get the styling note based on the personality score.
     */
    public static function getStylingNote(int $personality): string
    {
        if ($personality < 25) {
            return 'Worn without deliberateness — clothes exist, not styled.';
        }
        if ($personality < 45) {
            return 'Put together but not overthought — one considered choice, the rest just fits.';
        }
        if ($personality < 60) {
            return 'Casually considered — looks good without looking like effort.';
        }
        if ($personality < 78) {
            return 'Visibly intentional — pulling the look together on purpose, naturally.';
        }

        return 'Fully committed — every piece deliberate, confident in the choices.';
    }

    /**
     * Map the aesthetic vibe keywords to tags.
     */
    public static function getVibeTags(array $vibeWords): array
    {
        $vibeTagMap = config('prompts.VIBE_TAG_MAP', []);
        $tags = [];
        foreach ($vibeWords as $vibe) {
            if (isset($vibeTagMap[$vibe])) {
                foreach ($vibeTagMap[$vibe] as $tag) {
                    if (! in_array($tag, $tags, true)) {
                        $tags[] = $tag;
                    }
                }
            }
        }

        return $tags;
    }

    /**
     * Get a randomly selected accessory/prop.
     */
    public static function getProp(bool $noDrink = false): ?string
    {
        if ((mt_rand(0, 100) / 100) > 0.7) {
            return null;
        }

        $universalProps = config('prompts.UNIVERSAL_PROPS', []);
        $drinkPattern = '/latte|matcha|coffee|tea|smoothie|cup|straw|thermos|bubble/i';

        $options = $universalProps;
        if ($noDrink) {
            $options = array_filter($universalProps, function ($o) use ($drinkPattern) {
                return $o === null || ! preg_match($drinkPattern, $o);
            });
        }

        return self::randomElement($options);
    }

    /**
     * Build the photorealistic skin text block.
     */
    public static function buildSkinBlock(string $timeLabel, ?string $gender, ?string $physicalDesc): string
    {
        $pronoun = strtolower($gender ?? '') === 'male' ? 'his' : 'her';
        $p = strtolower($physicalDesc ?? '');
        $hasFair = str_contains($p, 'fair') || str_contains($p, 'pale') || str_contains($p, 'light skin');
        $hasDark = str_contains($p, 'dark skin') || str_contains($p, 'deep') || str_contains($p, 'ebony') || str_contains($p, 'melanin');

        $envReactions = [
            'golden hour late afternoon' => $hasFair
                ? 'warm golden flush across the forehead and cheekbone tops — more visible on fair skin, the lit side noticeably warm'
                : ($hasDark
                    ? 'rich deep skin tones dimensional in the golden light, the high points — forehead, cheekbone — catching warmth'
                    : 'faint sun-warmth across the forehead and tops of the cheekbones from afternoon outdoor exposure'),
            'overcast soft daylight' => 'even, cool-environment skin with a natural healthy flush — no sun warmth, no windburn',
            'bright sunny mid-morning' => 'slight sun-warmth flush on the forehead and nose bridge, micro-fine sweat at the temples catching the hard light',
            'blue-hour dusk' => 'faint cold-air redness around the nostrils and upper cheeks from the cooling evening temperature',
            'soft indoor afternoon window' => 'soft thermal indoor flush at the cheeks from the heated space, gentle warmth settling across the face',
            'morning indoor café' => 'a light warmth-flush as the skin adjusts from outdoor cool to the heated interior, slight color at the cheeks',
        ];

        $reaction = $envReactions[$timeLabel] ?? $envReactions['overcast soft daylight'];

        $imperfections = [
            'a small healing blemish on the left jaw, slightly pinker than surrounding skin',
            'faint asymmetric sun pigmentation near the right temple',
            'two freckles placed asymmetrically across the nose and left cheek',
            'a faint old thin scar below the right jawline — barely there, photographically real',
            'slight horizontal pressure line across the forehead from a hat worn earlier',
        ];
        $selectedImperfection = self::randomElement($imperfections);

        return "— Visible individual pores across {$pronoun} T-zone, nose, and cheeks; pores on the lit side cast tiny directional micro-shadows from the key light
— {$reaction}
— {$selectedImperfection}
— Left brow sits marginally higher than the right; one nostril slightly narrower; cupid's bow peaks uneven — natural asymmetry throughout
— Subtle digital sensor noise in the shadow areas consistent with iPhone auto-ISO";
    }

    /**
     * Get the character framing description based on personality.
     */
    public static function getCharacterFraming(int $personality): string
    {
        if ($personality < 30) {
            return 'quiet, inward energy — someone with a rich internal world, not performing for the camera. Real person, real life, not a model on a shoot';
        }
        if ($personality > 70) {
            return 'open, present energy — moves through the world with ease, comfortable being seen. Real person, real life, not a model on a shoot';
        }

        return 'natural, unhurried energy — comfortable in the moment. Real person, real life, not a model on a shoot';
    }

    /**
     * Get camera description.
     */
    public static function getCamera(): string
    {
        return "iPhone 16 Pro 24mm main lens f/1.78, held at arm's length or by a nearby friend, automatic exposure, natural sensor noise in shadow areas, slight lens barrel distortion at edges, 9:16 vertical, chest-up framing, face at the upper-third line, subject fills the center of the frame";
    }

    /**
     * Map personality to pose key.
     */
    public static function getPoseKeyFromPersonality(int $p): string
    {
        if ($p < 28) {
            return 'contemplative';
        }
        if ($p < 50) {
            return 'plandid';
        }
        if ($p < 70) {
            return (mt_rand(0, 99) > 49) ? 'plandid' : 'posed_cute';
        }

        return (mt_rand(0, 99) > 49) ? 'candid' : 'posed_cute';
    }

    /**
     * Get the descriptive text template for a standard pose.
     */
    public static function getPoseTemplate(string $poseKey, ?string $prop): string
    {
        $drinkPattern = '/latte|matcha|coffee|tea|smoothie|cup|straw|thermos|bubble/i';
        $isDrink = $prop && preg_match($drinkPattern, $prop);

        $poses = [
            'frontfacing' => 'body facing directly toward camera, weight shifted onto one leg for a subtle hip tilt — relaxed, not stiff. '.($prop ? "{$prop} held naturally in front of the body, cradled loosely in both hands at mid-chest" : 'arms relaxed at the sides or one hand resting loosely near the hip, fingers natural').'. Eyes meeting the lens directly with a quiet, present expression — not forced, not a performance. The "comfortable in front of the camera" framing. Face fully visible and front-lit.',

            'contemplative' => 'body facing 45° away from camera, weight forward on one leg. '.($prop ? "{$prop} held loosely at the side, almost forgotten" : 'hands relaxed loosely in front, fingers barely interlaced').'. Head turned back toward the lens mid-thought, eyes glancing toward but not fully meeting it — somewhere else mentally. A quiet, inward expression — not performing.',

            'plandid' => 'body angled 25–30° to camera, weight settled on the back leg, hips slightly offset. '.($prop ? "{$prop} held naturally in one hand, wrist relaxed" : 'one hand mid-loose-gesture near the hip, the other hanging naturally').'. Eyes glancing down-and-off-axis, 15° away from lens. Expression caught mid-thought — a specific private moment. The "noticed the camera half a second ago" framing.',

            'posed_cute' => 'body in soft 3/4 angle to camera, shoulders relaxed and slightly dropped. '.($prop ? "{$prop} held in both hands at chest height, elbows soft" : 'one hand gently touching the side of the jaw, fingers loose and natural').'. Eyes meeting the lens with a quiet small expression — a half-smile just forming, not fully committed. Posing but acting like she isn\'t.',

            'candid' => 'mid-action — caught at the apex of '.($isDrink ? "bringing the {$prop} toward the mouth, mid-sip, body naturally leaning slightly forward" : 'a genuine mid-laugh or bright spontaneous expression, body and shoulders caught in motion, one hand mid-gesture near the chest').'. Eyes looking directly toward the lens — spontaneous, unguarded eye contact full of real energy. Not posed, not looking away — the camera caught them in a real moment while they were already looking at it.',
        ];

        return $poses[$poseKey] ?? $poses['frontfacing'];
    }

    /**
     * Get the descriptive text template for a soul-safe pose.
     */
    public static function getPoseSoulTemplate(string $poseKey, ?string $prop): string
    {
        $poses = [
            'facing' => 'standing upright facing the camera, body straight and balanced, shoulders level, '.($prop ? "{$prop} held loosely at the side in one hand" : 'arms relaxed at the sides').' — calm and simple, not leaning, not posed',

            'angled' => 'standing upright with the body turned slightly toward the camera, balanced and straight, '.($prop ? "{$prop} held naturally in one hand at the side" : 'shoulders relaxed, arms at sides').' — simple and still, not leaning forward, not posed',

            'candid' => 'standing upright and looking toward the camera with a calm, natural expression, '.($prop ? "{$prop} held loosely in one hand at the side" : 'body balanced and still, arms relaxed').' — simple presence, not leaning, not mid-motion, not posed',
        ];

        return $poses[$poseKey] ?? $poses['facing'];
    }

    /**
     * Get the scene pool based on niches.
     */
    public static function getScenePool(array $niches): array
    {
        $scenePools = config('prompts.SCENE_POOLS', []);
        $allScenes = [];
        foreach ($scenePools as $pool) {
            $allScenes = array_merge($allScenes, $pool);
        }

        if (empty($niches)) {
            return $allScenes;
        }

        $nicheToSceneKey = [
            'fashion' => 'fashion',
            'beauty' => 'beauty',
            'lifestyle' => 'lifestyle',
            'fitness' => 'fitness',
            'travel' => 'travel',
            'food & dining' => 'lifestyle',
            'food' => 'lifestyle',
            'tech' => 'tech',
            'gaming' => 'gaming',
            'finance' => 'tech',
            'entertainment' => 'entertainment',
            'wellness' => 'fitness',
            'sports' => 'fitness',
            'sport' => 'fitness',
        ];

        $keys = [];
        foreach ($niches as $n) {
            $lowerN = strtolower($n);
            if (isset($nicheToSceneKey[$lowerN])) {
                $keys[] = $nicheToSceneKey[$lowerN];
            }
        }
        $keys = array_unique($keys);

        if (empty($keys)) {
            return $allScenes;
        }

        $pool = [];
        foreach ($keys as $k) {
            if (isset($scenePools[$k])) {
                $pool = array_merge($pool, $scenePools[$k]);
            }
        }

        return ! empty($pool) ? $pool : $allScenes;
    }

    /**
     * Build a single direct generation prompt.
     */
    public static function buildDirectPrompt(array|object $data, $forcePose = null, array $options = [], string $aspectRatio = '9:16'): string
    {
        $d = (array) $data;

        $gender = $d['gender'] ?? 'woman';
        $age = isset($d['age']) ? "{$d['age']} year old" : 'mid-20s';
        $backstory = trim($d['backstory'] ?? '');

        $physical = trim($d['physicalDesc'] ?? $d['physical_description'] ?? '');
        if ($physical === '') {
            $physical = 'with dark hair, warm complexion, natural features';
        }

        $vibes = (array) ($d['vibeWords'] ?? (isset($d['aesthetic_vibe']) ? [$d['aesthetic_vibe']] : []));
        $personality = isset($d['personality']) ? (int) $d['personality'] : 50;

        $isEditorial = in_array('Editorial', $vibes, true);

        $tier3Ctx = self::getBackstoryContext($physical, $backstory);

        if (isset($d['backstoryContext'])) {
            $backstoryCtx = [
                'buildHint' => $tier3Ctx['buildHint'],
                'physicalDetail' => $tier3Ctx['physicalDetail'],
                'lockedScene' => $tier3Ctx['lockedScene'],
                'tags' => (! empty($d['backstoryContext']['tags'])) ? $d['backstoryContext']['tags'] : $tier3Ctx['tags'],
                'sceneNiche' => $d['backstoryContext']['sceneNiche'] ?? $tier3Ctx['sceneNiche'],
                'dailyContext' => $d['backstoryContext']['dailyContext'] ?? null,
            ];
        } else {
            $backstoryCtx = $tier3Ctx;
        }

        $sceneNiche = $backstoryCtx['sceneNiche'] ?? null;
        $buildHint = $backstoryCtx['buildHint'] ?? null;
        $physicalDetail = $backstoryCtx['physicalDetail'] ?? null;
        $lockedScene = $backstoryCtx['lockedScene'] ?? null;

        $niches = $d['niches'] ?? (isset($d['niche']) ? (is_array($d['niche']) ? $d['niche'] : array_map('trim', explode(',', $d['niche']))) : []);

        $scenePools = config('prompts.SCENE_POOLS', []);

        if (! empty($options['backstoryLocked'])) {
            if ($lockedScene) {
                $scene = $lockedScene;
            } elseif ($sceneNiche && isset($scenePools[$sceneNiche])) {
                $scene = self::randomElement($scenePools[$sceneNiche]);
            } else {
                $scene = self::randomElement(self::getScenePool($niches));
            }
        } elseif (! empty($options['forceOutdoor'])) {
            $scene = self::randomElement(config('prompts.OUTDOOR_CANDID_SCENES', []));
        } elseif ($sceneNiche && isset($scenePools[$sceneNiche])) {
            $scene = self::randomElement($scenePools[$sceneNiche]);
        } else {
            $scene = self::randomElement(self::getScenePool($niches));
        }

        $timeConfigs = config('prompts.TIME_CONFIGS', []);
        $isOutdoor = preg_match('/\b(park|trail|beach|rooftop|street|pavement|outdoor|alley|plaza|market|terrace|harbor|path|square|city|urban|cobblestone|courtyard|sidewalk|promenade)\b/i', $scene);
        if ($isOutdoor) {
            $outdoorTimes = array_filter($timeConfigs, function ($t) {
                return ! str_contains($t['label'], 'indoor') && ! str_contains($t['label'], 'café');
            });
            $timeConfig = self::randomElement($outdoorTimes) ?: self::randomElement($timeConfigs);
        } else {
            $timeConfig = self::randomElement($timeConfigs);
        }

        $model = $options['model'] ?? 'gpt_image_2';
        if ($forcePose !== null) {
            $poseFn = $forcePose;
        } else {
            $poseFn = self::getPoseKeyFromPersonality($personality);
        }

        $prop = isset($options['forceProp']) ? $options['forceProp'] : self::getProp();

        $forcedProfTags = null;
        if (! empty($options['backstoryLocked']) && empty($vibes)) {
            if ($lockedScene) {
                $forcedProfTags = array_filter($backstoryCtx['tags'], function ($t) {
                    return $t !== 'casual' && $t !== 'urban' && $t !== 'street';
                });
            } else {
                $forcedProfTags = $backstoryCtx['tags'];
            }
        }

        $wardrobeBase = self::selectWardrobe($gender, $vibes, $personality, $physical, $backstory, $forcedProfTags);

        $paletteLine = '';
        if ($model !== 'soul_2') {
            $vibePaletteMap = config('prompts.VIBE_PALETTE_MAP', []);
            $palettes = [];
            foreach ($vibes as $v) {
                if (isset($vibePaletteMap[$v])) {
                    $palettes[] = $vibePaletteMap[$v];
                }
            }
            $paletteLine = implode(' | ', $palettes);
        }

        $wardrobe = ($paletteLine !== '') ? "{$wardrobeBase}\n{$paletteLine}" : $wardrobeBase;
        $camera = self::getCamera();
        $skinBlock = self::buildSkinBlock($timeConfig['label'], $gender, $physical);
        $characterFraming = self::getCharacterFraming($personality);

        $propDesc = $prop
            ? "{$prop} held in one hand — no visible brand logo"
            : 'hands in a natural mid-gesture, nothing held';

        $buildDesc = (! empty($options['backstoryLocked']) && $buildHint && empty($d['build'])) ? ", {$buildHint}" : '';
        $physicalDetailStr = (! empty($options['backstoryLocked']) && $physicalDetail) ? ", {$physicalDetail}" : '';

        if ($model === 'soul_2') {
            $poseTemplate = self::getPoseSoulTemplate($poseFn, $prop);
        } else {
            $poseTemplate = self::getPoseTemplate($poseFn, $prop);
        }

        $poseNames = [
            'frontfacing' => 'iPhone portrait — direct, relaxed, facing camera',
            'contemplative' => 'iPhone portrait — quiet, present, facing camera',
            'plandid' => 'iPhone candid — soft awareness, facing camera',
            'posed_cute' => 'iPhone feed shot — soft pose, eyes at lens',
            'candid' => 'iPhone candid — mid-moment, eyes at the lens',
            'facing' => 'iPhone portrait — direct, relaxed, facing camera',
            'angled' => 'iPhone portrait — quiet, present, facing camera',
        ];
        $poseName = $poseNames[$poseFn] ?? 'iPhone candid — mid-moment, eyes at the lens';

        $landscapeText = ($aspectRatio === '16:9')
            ? 'Horizontal landscape frame — subject standing close to camera, filling at least half the frame height, environment visible on both sides. Not a distant wide shot — the subject must be close enough that face and outfit detail are fully legible. This is a wide candid, not a portrait crop rotated sideways.'
            : 'Subject fills 60–70% of the 9:16 frame — tight crop, not a wide environmental shot.';

        $editorialText = $isEditorial ? 'Editorial vibe applies to the styling only — the photo itself is a raw iPhone snapshot.' : '';

        return "Photograph style: iPhone 16 Pro snapshot. Taken by the subject or a nearby friend, handheld, automatic settings. No professional crew, no studio, no lighting setup, no direction given. Raw iPhone output — unedited. The subject is unaware this will be published — a personal photo, not intended for any shoot.

Scene: {$scene}".(! empty($options['backstoryLocked']) && $lockedScene ? '' : ", {$timeConfig['label']}").". Empty of other people. If the location is an interior, it shows real signs of habitation — not a styled showroom. Background is real, in-focus, and unmanipulated exactly as an iPhone captures it — no blur, no bokeh, no artificial depth of field. The subject is the hero through tight framing and natural lighting, not through background manipulation.

Subject: {$gender}, {$age}, {$physical}{$buildDesc}{$physicalDetailStr}. {$characterFraming}. Natural micro-asymmetries in the face — this is a real iPhone photograph of a real person, not a 3D render or CGI. Real visible pore texture on the nose, cheeks, and forehead — and on all exposed skin including arms, neck, and shoulders. Zero skin smoothing anywhere on the body, zero airbrushing, zero beauty filter applied.

Pose: {$poseTemplate} {$propDesc}.

Wardrobe & details: {$wardrobe}

Lighting: {$timeConfig['lighting']} The subject's face is the brightest element in the frame. This is natural found light — not a lighting setup.

Camera & capture: {$camera}.

Skin (rendered as concrete photographic facts, not category words):
{$skinBlock}

Use case: {$poseName}

Constraints: no people in the background. No visible brand logos on any item. {$landscapeText} No background blur or bokeh. Real pore texture and skin imperfections visible on the face and all exposed body skin — zero beauty retouching. No AI aesthetic markers: no unnaturally bright irises, no perfectly symmetrical face, no plastic-smooth skin, no uncanny glow. No phone screen, no social media UI, no app overlay, no notification bar, no status bar, no interface elements of any kind visible anywhere in the image. This is a raw photograph — no digital overlays, no framing devices, no UI chrome. {$editorialText}";
    }

    /**
     * Build three variation prompts for an influencer.
     */
    public static function buildThreeVariationPrompts(array|object $data, string $aspectRatio = '9:16', string $model = 'gpt_image_2'): array
    {
        $d = (array) $data;

        $drinkUsed = false;
        $drinkPattern = '/latte|matcha|coffee|tea|smoothie|cup|straw|thermos|bubble/i';

        $sessionProp = function (bool $noDrink = false) use (&$drinkUsed, $drinkPattern) {
            $p = self::getProp($noDrink || $drinkUsed);
            if ($p !== null && preg_match($drinkPattern, $p)) {
                $drinkUsed = true;
            }

            return $p;
        };

        if ($model === 'soul_2') {
            return [
                self::buildDirectPrompt($d, 'facing', ['model' => 'soul_2', 'forceProp' => $sessionProp()], $aspectRatio),
                self::buildDirectPrompt($d, 'angled', ['model' => 'soul_2', 'forceProp' => $sessionProp()], $aspectRatio),
                self::buildDirectPrompt($d, 'candid', ['model' => 'soul_2', 'forceOutdoor' => true, 'forceProp' => $sessionProp(true)], $aspectRatio),
            ];
        }

        $physical = $d['physicalDesc'] ?? $d['physical_description'] ?? '';
        $backstory = $d['backstory'] ?? '';
        $tier3Ctx = self::getBackstoryContext($physical, $backstory);
        $hasBackstoryLock = ! empty($tier3Ctx['sceneNiche']) || ! empty($tier3Ctx['lockedScene']) || ! empty($d['backstoryContext']['sceneNiche']);

        return [
            self::buildDirectPrompt($d, 'frontfacing', ['forceProp' => $sessionProp()], $aspectRatio),
            self::buildDirectPrompt($d, 'posed_cute', ['forceProp' => $sessionProp()], $aspectRatio),
            $hasBackstoryLock
                ? self::buildDirectPrompt($d, 'candid', ['backstoryLocked' => true, 'forceProp' => null], $aspectRatio)
                : self::buildDirectPrompt($d, 'candid', ['forceOutdoor' => true, 'forceProp' => $sessionProp(true)], $aspectRatio),
        ];
    }

    /**
     * Resolve the physical description string from an influencer instance or array.
     */
    private static function resolvePhysicalDesc(array|object $influencer): string
    {
        $props = [];
        if ($influencer instanceof Model) {
            if (isset($influencer->properties)) {
                $props = $influencer->properties;
            }
        } elseif (is_object($influencer)) {
            $props = $influencer->properties ?? $influencer;
        } else {
            $props = $influencer['properties'] ?? $influencer;
        }

        if ($props instanceof InfluencerProperties) {
            $propsArray = $props->toArray();
        } else {
            $propsArray = (array) $props;
        }

        $physicalDesc = $propsArray['physical_description'] ?? $propsArray['physicalDesc'] ?? '';
        if (empty($physicalDesc)) {
            $physicalDesc = self::buildPhysicalDescString($propsArray);
        }

        return $physicalDesc;
    }

    /**
     * Build the professional studio headshot prompt.
     */
    public static function buildCloseUpPrompt(array|object $influencer): string
    {
        $physicalDesc = self::resolvePhysicalDesc($influencer);
        $phys = (! empty($physicalDesc)) ? "The subject: {$physicalDesc}. " : '';

        return "Professional studio headshot. Subject facing directly forward, eyes looking straight into the camera lens. Framed from shoulders up — head, neck, and upper chest visible. Clean seamless pure white backdrop, soft gradient toward very light grey at edges, no texture, no cast shadows on background.

{$phys}Soft diffused studio lighting: two large softboxes at 45-degree angles producing soft, even, shadow-free illumination across the face. Subtle catchlights visible in both eyes. No harsh under-nose or chin shadows. Skin tone reproduced accurately — natural pore texture, subtle imperfections visible, zero retouching.

Replicate every physical detail from the reference image exactly: facial bone structure, unique facial features and natural asymmetries, precise skin tone, freckles, moles, iris color and detail, eyebrow shape, lip shape, hair color, texture and natural fall. The subject must be unmistakably the same individual.

Subject standing straight, head completely level, facing dead-on into the camera — no tilt, no turn, no pose. Eyes looking directly into the lens. Neutral expression, mouth relaxed and closed. No modelling, no attitude, no special pose whatsoever. Identical to a casting reference or identity card photo.

Shot on Phase One IQ4 150MP, 85mm portrait lens, f/2.8, studio strobe. Photorealistic, ultra-sharp facial detail, RAW photograph quality. Studio identity reference portrait.";
    }

    /**
     * Build the beauty model feature reference sheet prompt.
     */
    public static function buildFeatureSheetPrompt(array|object $influencer): string
    {
        $physicalDesc = self::resolvePhysicalDesc($influencer);
        $phys = (! empty($physicalDesc)) ? "The subject: {$physicalDesc}. " : '';

        return "Beauty model feature reference sheet. {$phys}Pure white background throughout. Clinical reference card layout — like a casting or makeup artist reference sheet printed on white paper. Bold black uppercase sans-serif labels above each panel. Clear white gutters between every panel and white margins around the outside.

Layout — 4 rows stacked top to bottom:
Row 1 (full width): one wide panel labelled \"EYE\" — extreme macro close-up centered tightly on both irises. The irises fill the majority of the frame. Shows exact iris color, pattern, and detail. Lashes visible at edges but irises are the dominant subject.
Row 2 (full width): one wide panel labelled \"BROW\" — close-up from hairline to mid-nose showing exact brow shape, arch, thickness, hair direction, forehead skin.
Row 3 (two equal side-by-side panels):
  Left — labelled \"LIP\": close-up from nose base to chin showing exact lip shape, cupid's bow, natural lip color.
  Right — labelled \"SKIN TEXTURE\": macro close-up of cheek skin showing pores, freckles, natural skin detail, zero retouching.
Row 4 (two equal side-by-side panels):
  Left — labelled \"HAIR TEXTURE\": close-up of hair strands showing exact color, shine, texture, wave or curl pattern.
  Right — labelled \"HANDS\": close-up of hand showing nail shape, length, nail color or nail art, knuckle skin detail.

Replicate the reference person's exact features in every panel: precise skin tone, freckle placement, hair color, lip shape, brow arch. Zero beauty retouching — raw photographic detail. White space clearly visible between all panels.

Photorealistic RAW photograph quality, ultra-sharp macro detail in each panel. Shot on Hasselblad 100mm macro lens.";
    }

    /**
     * Build the professional character turnaround sheet prompt.
     */
    public static function buildInfluencerSheetPrompt(array|object $influencer): string
    {
        $props = [];
        $backstory = '';
        $clothingStyle = '';

        if ($influencer instanceof Model) {
            $backstory = $influencer->bio ?? '';
            if (isset($influencer->properties)) {
                $props = $influencer->properties;
            }
        } elseif (is_object($influencer)) {
            $backstory = $influencer->backstory ?? $influencer->bio ?? '';
            $props = $influencer->properties ?? $influencer;
        } else {
            $backstory = $influencer['backstory'] ?? $influencer['bio'] ?? '';
            $props = $influencer['properties'] ?? $influencer;
        }

        if ($props instanceof InfluencerProperties) {
            $propsArray = $props->toArray();
        } else {
            $propsArray = (array) $props;
        }

        $physicalDesc = self::resolvePhysicalDesc($influencer);

        $clothingStyle = $propsArray['aesthetic_vibe'] ?? $propsArray['aestheticVibe'] ?? '';
        if (empty($clothingStyle) && isset($propsArray['clothingStyle'])) {
            $clothingStyle = $propsArray['clothingStyle'];
        }

        $phys = (! empty($physicalDesc)) ? "The character: {$physicalDesc}. " : '';
        $style = (! empty($clothingStyle)) ? "Outfit: {$clothingStyle}. " : '';

        $backstory = trim($backstory !== '' ? $backstory : ($propsArray['backstory'] ?? ''));
        $ctx = '';
        if ($backstory !== '') {
            $slicedBackstory = mb_substr($backstory, 0, 300);
            $ctx = "Character background: {$slicedBackstory}. ";
        }

        return "Professional full-body character turnaround sheet. Pure white background, no background elements whatsoever. Soft neutral studio lighting, perfectly flat and even across all four panels — no shadows, no color cast, no vignette. Neutral facial expression, neutral pose.

{$phys}{$style}{$ctx}

Single row of four equally sized full-body shots from head to toe, each with a small label in clean sans-serif capitals printed above the figure:
Panel 1 — \"FRONT VIEW\": character facing directly forward, arms relaxed at sides, feet together.
Panel 2 — \"SIDE VIEW\": character in perfect left profile, arms at sides.
Panel 3 — \"BACK VIEW\": character facing directly away, arms relaxed.
Panel 4 — \"THREE-QUARTER VIEW\": character at 45-degree angle facing forward-right.

Replicate every single physical detail identically across all four panels: exact facial structure and bone structure, unique facial features and natural asymmetries, precise skin tone, real pore texture, natural blemishes, freckles, moles, birthmarks, natural moisture and skin sheen, realistic catchlights in the eyes, exact iris color and detail, exact hair color and texture and styling. Zero beauty retouching — raw skin imperfections must be visible. Same outfit, same proportions, same scale in every panel.

Shot on Hasselblad X2D 100C, photorealistic, ultra-sharp micro detail, RAW photograph quality. Character design sheet, model sheet, orthographic turnaround reference.";
    }

    /**
     * Helper to select a random element from an array.
     */
    private static function randomElement(array $array)
    {
        if (empty($array)) {
            return null;
        }

        return $array[array_rand($array)];
    }

    /**
     * Build the Photo Studio prompt.
     */
    public static function buildPhotoStudioPrompt(array $args): string
    {
        $influencer = $args['influencer'] ?? null;
        $location = $args['location'] ?? 'coffee-shop';
        $timeOfDay = $args['timeOfDay'] ?? 'afternoon';
        $pose = $args['pose'] ?? 'front';
        $vibe = $args['vibe'] ?? '';
        $wardrobeText = $args['wardrobeText'] ?? null;
        $hairstyleText = $args['hairstyleText'] ?? null;
        $outfitPreset = $args['outfitPreset'] ?? null;
        $stance = $args['stance'] ?? '';
        $aspectRatio = $args['aspectRatio'] ?? '9:16';
        $expression = $args['expression'] ?? 'natural';
        $gaze = $args['gaze'] ?? 'at-camera';
        $propText = $args['propText'] ?? null;
        $propRefs = $args['propRefs'] ?? [];
        $faceTag = $args['faceTag'] ?? null;
        $wardrobeTag = $args['wardrobeTag'] ?? null;
        $closeUp1Tag = $args['closeUp1Tag'] ?? null;
        $closeUp2Tag = $args['closeUp2Tag'] ?? null;
        $poseTag = $args['poseTag'] ?? null;
        $locationText = $args['locationText'] ?? null;
        $poseText = $args['poseText'] ?? null;
        $variationIdx = $args['variationIdx'] ?? null;

        $isSitting = $stance === 'sitting';

        // ── Shot type opener ──────────────────────────────────────────────
        $shotType = 'Candid iPhone photo';
        if ($vibe === 'editorial') {
            $shotType = 'Editorial photo';
        } elseif ($vibe === 'luxury') {
            $shotType = 'Luxury lifestyle photo';
        } elseif ($vibe === 'street') {
            $shotType = 'Street style photo';
        }

        // ── Subject — ref tag if available, minimal text fallback ─────────
        $gender = 'Female';
        $ageVal = '';
        $physicalDesc = '';
        if ($influencer) {
            if (is_array($influencer)) {
                $gender = $influencer['gender'] ?? 'Female';
                $ageVal = $influencer['age'] ?? '';
            } else {
                $gender = $influencer->gender ?? 'Female';
                $ageVal = $influencer->age ?? '';
            }
            $physicalDesc = self::resolvePhysicalDesc($influencer);
        }

        $age = ! empty($ageVal) ? "{$ageVal}-year-old" : 'young';
        $genderNoun = strtolower($gender) === 'male' ? 'man' : 'woman';
        $physicalDescStr = ! empty($physicalDesc) ? "a {$age} {$genderNoun}, {$physicalDesc}" : "a {$age} {$genderNoun}";

        if ($faceTag) {
            $subject = "the subject from {$faceTag} ({$physicalDescStr})";
        } else {
            $subject = $physicalDescStr;
        }

        // ── Wardrobe ───
        $outfitPresetMapFemale = [
            'Casual' => 'High-waist straight-leg jeans, a fitted white tee or ribbed tank tucked at the front, white leather sneakers or ballet flats. Effortless and put-together.',
            'Streetwear' => 'Baggy cargo pants or wide-leg denim, an oversized graphic tee or cropped hoodie, chunky sneakers, a fitted cap or bucket hat. Bold and urban.',
            'Chic' => 'Tailored wide-leg trousers or a midi wrap skirt, a fitted silk or satin blouse, pointed-toe kitten heels or mules, delicate gold jewellery. Polished and intentional.',
            'Athleisure' => 'High-waist leggings or biker shorts, a cropped sports bra or fitted zip-up hoodie, clean white trainers. Gym-to-street energy.',
            'Minimal' => 'Neutral tones only — cream, beige, stone, white. A clean ribbed tank or fitted turtleneck, straight-leg trousers or a long linen skirt, simple leather sneakers or mules. Nothing extra.',
            'Glam' => 'A slinky satin slip dress or a fitted co-ord set in a jewel tone or champagne, strappy stilettos, statement drop earrings, a small evening clutch. Dressed up.',
            'Party' => 'A bodycon mini dress or a spaghetti-strap top with low-rise straight jeans, heeled ankle boots or platform sandals, a small chain-strap bag. Night-out confident.',
            'Cozy' => 'An oversized chunky knit cardigan or hoodie over a longline tee, soft wide-leg sweatpants or joggers, fluffy socks, slide sandals. Stay-home comfortable.',
        ];

        $outfitPresetMapMale = [
            'Casual' => 'Well-fitted straight-leg jeans or chinos in a neutral tone, a clean crew-neck tee or lightweight chambray shirt, white leather sneakers. Everyday sharp.',
            'Streetwear' => 'Baggy cargo pants or wide-leg denim, an oversized graphic tee or heavyweight hoodie, chunky sneakers, a fitted cap or beanie. Urban and confident.',
            'Smart Casual' => 'Slim dark jeans or tapered chinos, an untucked linen or Oxford button-down with sleeves rolled up, clean suede loafers or leather trainers. Effortlessly put-together.',
            'Athleisure' => 'Tapered joggers or training shorts, a fitted moisture-wicking tee or quarter-zip pullover, clean running shoes. Ready to move.',
            'Minimal' => 'Neutral palette — black, white, grey, stone. A fitted crewneck or Henley, slim straight trousers, simple clean sneakers or Chelsea boots. No logos, nothing extra.',
            'Business' => 'Slim-fit chinos or tailored trousers, a tucked Oxford shirt with the collar open, a sharp unstructured blazer in navy or camel, leather loafers or clean dress shoes.',
            'Party' => 'Dark slim jeans or tailored trousers, a fitted satin or textured button-up left partially open, leather Chelsea boots or clean dress shoes, a simple watch.',
            'Cozy' => 'An oversized knit sweater or zip-up hoodie, relaxed wide-leg sweatpants or joggers, thick crew socks, slide sandals or low-profile sneakers.',
        ];

        $outfitMap = strtolower($gender) === 'male' ? $outfitPresetMapMale : $outfitPresetMapFemale;
        $hasHairstyleOverride = ! empty(trim($hairstyleText ?? ''));

        $wardrobe = '';
        if ($wardrobeTag) {
            $hairMatch = $hasHairstyleOverride ? '' : '. Match the hairstyle from this reference exactly';
            $wardrobe = "the complete outfit from {$wardrobeTag} — reproduce every item exactly as shown: all clothing, headwear, and accessories must match the reference. If headwear is present in the reference, it must be worn on the head{$hairMatch}";
        } else {
            $trimmedWardrobeText = trim($wardrobeText ?? '');
            if (! empty($trimmedWardrobeText)) {
                $wardrobe = $trimmedWardrobeText;
            } elseif ($outfitPreset && $outfitPreset !== 'current' && isset($outfitMap[$outfitPreset])) {
                $wardrobe = $outfitMap[$outfitPreset];
            } else {
                $wardrobe = 'their current outfit';
            }
        }

        // ── Close-up detail line (skin texture + face detail) ────────────
        $closeUpLine = '';
        if ($closeUp1Tag && $closeUp2Tag) {
            $closeUpLine = "Match skin texture and facial detail from {$closeUp1Tag} and {$closeUp2Tag}.";
        } elseif ($closeUp1Tag) {
            $closeUpLine = "Match skin texture and facial detail from {$closeUp1Tag}.";
        }

        // ── Pose ───
        $candidActions = [
            'caught mid-sip, raising a drink to their lips',
            'caught mid-step, walking naturally',
            'caught mid-reach, grabbing something nearby',
            'looking down at their phone',
            'caught adjusting their hair',
        ];

        $poseMap = [
            'plandid' => 'Natural plandid moment — caught in a real activity, not posing. Interacting with the environment, not looking at the camera.',
            'candid' => null,
            'handheld' => 'Handheld selfie — one arm extended straight toward the camera, phone gripped in that hand with fingers curling around it, pointing directly at the lens. Shot from the phone\'s perspective: the subject fills most of the frame, arm foreshortened toward the viewer. Looking straight into the front-facing lens. Other arm relaxed at the side or resting naturally. Natural slight-below-eye-level selfie angle. Candid and personal.',
            'cute-posed' => 'Cute posed stance — one hand lightly touching the hair or face, soft and natural.',
            'walking' => 'Mid-stride, walking naturally, completely off the camera.',
            'mid-turn' => 'Caught mid-turn, as if just hearing their name called — body still turning, head looking back.',
            'front' => 'Facing the camera directly, confident and composed.',
            'hip-pop' => 'Hip pop pose — natural S-curve with one hip shifted out to the side.',
            'triangle' => 'One hand on hip, natural triangle shape with the arm, relaxed fashion pose.',
            'over-shoulder' => 'Body turned away from camera, looking back over the shoulder.',
            'facing-away' => 'Fully facing away from the camera — back to the lens, both hands clasped together behind the back, standing with a subtle relaxed forward tilt, head looking forward or slightly down. Shot from behind. Completely unaware of being photographed.',
            'long-line' => 'Tall elegant fashion pose, one leg extended forward, long clean line through the body.',
            'lean' => 'Leaning casually against a wall or surface, relaxed and at ease.',
            'hands-pockets' => 'Hands in pockets, relaxed and natural, not performing.',
            'crossed-arms' => 'Arms crossed, confident and settled.',
            'seated-casual' => 'Seated casually, relaxed and natural.',
            'seated-extended' => 'Seated with legs extended out in front, relaxed and elongated.',
            'seated-crossed' => 'Seated cross-legged, comfortable and grounded.',
            'seated-lean' => 'Seated and leaning slightly forward, relaxed and engaged.',
        ];

        $trimmedPoseText = trim($poseText ?? '');
        $basePose = '';
        if (! empty($trimmedPoseText)) {
            $basePose = $trimmedPoseText;
        } elseif ($pose === 'candid') {
            $idx = $variationIdx !== null ? intval($variationIdx) : rand(0, count($candidActions) - 1);
            $action = $candidActions[$idx % count($candidActions)];
            $basePose = "Candid pose — {$action}. Unaware of the camera.";
        } elseif ($pose && isset($poseMap[$pose]) && $poseMap[$pose] !== null) {
            $basePose = $poseMap[$pose];
        } else {
            $basePose = 'relaxed natural posture';
        }

        $stancePrefix = $isSitting
            ? 'Subject is clearly seated. '
            : 'Subject is standing upright on both feet — not sitting, not crouching. ';
        $poseDesc = $stancePrefix.$basePose;

        // ── Expression ───
        $expressionMap = [
            'natural' => '',
            'smiling' => 'Expression: a genuine soft smile — lips parted slightly, upper teeth just visible, the smile reaching the outer corners of the eyes with warmth.',
            'laughing' => 'Expression: caught mid-laugh at the apex — head tilted slightly back, eyes fully crinkled with genuine amusement, mouth open, the laugh spontaneous and unposed.',
            'serious' => 'Expression: direct and serious — neutral mouth at rest, steady gaze into the lens, no smile. Composed and self-assured.',
            'looking-away' => '',
        ];

        $expressionDesc = '';
        if (isset($expressionMap[$expression])) {
            $expressionDesc = $expressionMap[$expression];
        } else {
            $trimmedExpr = trim($expression);
            if (! empty($trimmedExpr)) {
                $expressionDesc = "Expression: {$trimmedExpr}.";
            }
        }

        // ── Gaze direction ───
        $faceAwayPose = $pose === 'facing-away';
        $gazeDesc = (! $faceAwayPose && $gaze === 'looking-away')
            ? 'Eyes directed off-axis — looking to the side or slightly above the camera, as if unaware of being photographed.'
            : '';

        // ── Props ───
        $propDesc = '';
        $trimmedPropText = trim($propText ?? '');
        if (count($propRefs) > 0 || ! empty($trimmedPropText)) {
            $gPron = strtolower($gender) === 'male' ? 'his' : (strtolower($gender) === 'female' ? 'her' : 'their');
            $hand = "{$gPron} hand";
            $lines = [];

            $holdingRefs = array_filter($propRefs, fn ($r) => ($r['mode'] ?? '') === 'holding');
            $wearingRefs = array_filter($propRefs, fn ($r) => ($r['mode'] ?? '') === 'wearing');

            if (count($holdingRefs) > 0) {
                $tags = implode(' and ', array_map(fn ($r) => $r['tag'], $holdingRefs));
                $refLabelS = count($holdingRefs) > 1 ? 's' : '';
                $lines[] = "Holding the single product shown in {$tags} in {$hand} — one item, gripped naturally and clearly visible. It is held in hand only, not worn or placed anywhere on the body. Match every product detail, colour, and branding exactly to the reference{$refLabelS}.";
                $lines[] = ucfirst($gPron)." other hand is completely empty and relaxed at {$gPron} side — absolutely nothing else held, carried, or placed in either hand.";
                if ($wardrobeTag) {
                    $lines[] = "IMPORTANT — this held product is a completely separate item from the outfit in {$wardrobeTag}. Any headwear, cap, hat, or accessory that appears in the outfit reference is part of the outfit and must still be worn on the body exactly as shown. The held product in {$tags} is an additional prop in the hand — do not conflate it with outfit items, and do not remove or omit any part of the outfit because of this prop.";
                }
            }

            foreach ($wearingRefs as $r) {
                $tag = $r['tag'] ?? '';
                $lines[] = "Wearing the item from {$tag} on the body — it is worn, not held in hand, not carried. Match every product detail, colour, and branding exactly as shown in the reference.";
            }

            if (count($propRefs) > 0) {
                $lines[] = 'Product consistency is critical — the item must be identical to the reference.';
            }
            if (! empty($trimmedPropText)) {
                $lines[] = count($propRefs) > 0 ? $trimmedPropText : "Hands: {$trimmedPropText}.";
            }
            $propDesc = implode(' ', $lines);
        }

        // ── Scene variants ───
        $sceneVariants = [
            'coffee-shop' => [
                'Near the front window, street traffic soft and blurred outside the glass, a flat white on the small table beside her.',
                'Standing at the café counter, the espresso machine just behind, ceramic cups stacked on the shelf, the barista area softly out of focus.',
                'In a cosy corner of the café, a trailing plant catching the light, a low shelf with books visible to one side.',
                'In the open café floor, chairs and tables surrounding her, a chalkboard menu visible on the far wall.',
            ],
            'city-street' => [
                'A wide pedestrian pavement, shopfronts with awnings receding behind her, a couple of parked cars at the kerb.',
                'A narrow side street, worn brick walls on either side, a tiled doorway or iron gate partially visible behind her.',
                'At a busy intersection corner, a pedestrian crossing ahead, taxis and delivery bikes soft and distant behind.',
                'Standing outside a boutique entrance, the lit shop window display visible to one side, the street stretching ahead of her.',
            ],
            'beach' => [
                'At the waterline, the ocean stretching flat to the horizon directly behind her, wet sand at her feet.',
                'Higher up the dry beach near the dunes, beach grass or low scrub visible in the far background.',
                'Near a weathered lifeguard tower or beach bar structure, the timber frame partially in frame, the beach spreading beyond.',
                'On a beach boardwalk, wooden planks underfoot, the beach and ocean visible below and behind.',
            ],
            'rooftop' => [
                'At the rooftop ledge, hands near the concrete parapet, the city grid stretching to the horizon behind her.',
                'Near a water tower structure on the roof, the tower framing one side of the background.',
                'In a rooftop garden area, terracotta pots and low trailing greenery around her, the skyline behind.',
                'Under a timber pergola with string lights threaded overhead, the city glowing behind her in the distance.',
            ],
            'bedroom' => [
                'Standing by the window, curtains half-drawn, natural light falling across from one side, the bed and nightstand soft behind.',
                'In front of the full-length mirror or dresser, the room reflected behind her, jewellery and a perfume bottle on the surface.',
                'Near the bed, the rumpled duvet and stacked pillows in the background, a bedside lamp just visible to one side.',
                'By the open wardrobe or bedroom door, clothes loosely visible inside, a floor lamp casting warm light in the corner.',
            ],
            'bathroom' => [
                'Directly facing the bathroom mirror, the room reflected behind her, products lined along the counter.',
                'Side-on near the sink, the tap and basin just visible, a shelf of skincare products in the background.',
                'Against the tiled wall beside the shower screen, steam or condensation present, a towel on the rail behind.',
                'At a vanity with lit mirror surrounds, the mirror frame catching the light, products and a small candle arranged beside.',
            ],
            'mall' => [
                'In the centre of a wide mall corridor, storefronts on both sides receding symmetrically behind her.',
                'Just outside a store entrance, the shop window display softly glowing to one side of the frame.',
                'On an upper floor landing near an escalator, the levels of the mall visible below.',
                'In a quiet mall atrium, a large overhead skylight above, marble floors reflecting the ambient light below.',
            ],
            'gym' => [
                'In the free weights area, a dumbbell rack and weight plates partially visible in the soft background.',
                'In front of the floor-to-ceiling mirrored gym wall, her reflection and the equipment receding behind her.',
                'Near a cable machine or a flat bench, other equipment visible and blurred in the background.',
                'By the gym entrance or water station, a reception counter or locker bank softly visible behind her.',
            ],
            'park' => [
                'On a paved park path, mature trees lining both sides and the path curving away behind her.',
                'On the open grass beside a large tree, the trunk partially at the edge of frame, the lawn and sky beyond.',
                'Near a park bench, the wooden bench just visible behind her, a flower bed or low hedge in the distance.',
                'On a stone bridge over a small pond or near a fountain, the water and surrounding greenery soft behind.',
            ],
            'restaurant' => [
                'At a window table, the street or garden just visible through the glass behind her, linen on the table.',
                'In the centre of the dining room, dressed tables and chairs stretching away in the warm background.',
                'At the bar end of the restaurant, bottles and the bar-back shelving softly visible behind her.',
                'In a corner booth, slightly dimmer and more intimate, a candle on the table, the main dining room beyond.',
            ],
            'hotel' => [
                'At the floor-to-ceiling window, the city skyline or landscape soft and luminous behind her.',
                'Near the bed, the upholstered headboard and crisp white pillows composing the background.',
                'At the hotel room desk or dressing table, the large wall mirror reflecting the room behind her.',
                'In the carpeted hotel corridor just outside the room, the hallway receding with wall sconces behind her.',
            ],
            'studio' => [
                'Against a white seamless paper backdrop, nothing but clean negative space behind and below.',
                'In front of a large north-facing window, soft daylight from one side, plain white walls all around.',
                'Near a bare metal clothing rail, a few garments loosely hanging just to one side of the frame.',
                'In an open studio loft, exposed brick or industrial ceiling details soft in the background behind her.',
            ],
        ];

        $trimmedLocText = trim($locationText ?? '');
        $scene = '';
        $locationLabel = '';
        if (! empty($trimmedLocText)) {
            $locationLabel = 'The location is '.trim($trimmedLocText).'.';
        } else {
            if ($location && isset($sceneVariants[$location])) {
                $variants = $sceneVariants[$location];
                $vIdx = $variationIdx !== null ? intval($variationIdx) : rand(0, count($variants) - 1);
                $scene = $variants[$vIdx % count($variants)];
            }
            $locationLabelMap = [
                'coffee-shop' => 'The location is a coffee shop interior.',
                'city-street' => 'The location is an outdoor city street.',
                'beach' => 'The location is a beach outdoors.',
                'rooftop' => 'The location is a rooftop terrace.',
                'bedroom' => 'The location is a private bedroom interior.',
                'bathroom' => 'The location is a bathroom — this is a mirror selfie.',
                'mall' => 'The location is inside a shopping mall.',
                'gym' => 'The location is inside a gym.',
                'park' => 'The location is an outdoor park.',
                'restaurant' => 'The location is inside a restaurant.',
                'hotel' => 'The location is a hotel room interior.',
                'studio' => 'The location is a photography studio.',
            ];
            $locationLabel = $locationLabelMap[$location] ?? ($location && ! empty(trim($location)) ? 'The location is '.trim($location).'.' : '');
        }

        // ── Background people ───
        $backgroundPeople = [
            'No other people in frame.',
            'One or two blurred figures in the far background, naturally present in the environment, completely out of focus.',
            'No other people in frame.',
            'A few soft figures visible in the background going about their day, entirely out of focus and incidental.',
        ];

        $peopleLine = ($location === 'bathroom' || $location === 'bedroom' || $location === 'hotel')
            ? 'No other people in frame.'
            : $backgroundPeople[$variationIdx !== null ? (intval($variationIdx) % count($backgroundPeople)) : 0];

        $timeAtmoMap = [
            'morning' => 'Time of day: early morning — the sun has just risen, low on the horizon, the world is quiet and calm, air is cool and still.',
            'afternoon' => 'Time of day: midday afternoon — the sun is high overhead, full bright daylight, warm temperatures, the day at its most active.',
            'golden-hour' => 'Time of day: golden hour — the sun is sitting just at the horizon, warm amber and orange tones flood the entire scene, long soft shadows stretching across everything.',
            'night' => 'Time of day: night — it is fully dark outside, the sky is black or deep navy, zero natural light, all illumination comes from artificial sources in the environment.',
        ];
        $timeAtmo = $timeAtmoMap[$timeOfDay] ?? '';

        $shortLighting = [
            'coffee-shop' => [
                'morning' => 'Soft morning window light from one side, cool and directional.',
                'afternoon' => 'Warm filtered window light, slight tungsten from overhead pendants.',
                'golden-hour' => 'Low warm golden-hour sun through the window, amber and tungsten mixing.',
                'night' => 'Warm tungsten pendant overhead as key, intimate and dim.',
            ],
            'city-street' => [
                'morning' => 'Cool early skylight, thin strip of low sun catching one shoulder.',
                'afternoon' => 'Hard direct sun from front-left, sharp clean shadows.',
                'golden-hour' => 'Low warm backlight rimming the hair, cool sky-bounce fill on the face.',
                'night' => 'Warm sodium streetlamp overhead, cool blue twilight, neon accents in background.',
            ],
            'beach' => [
                'morning' => 'Cool soft overcast skylight, even and shadowless.',
                'afternoon' => 'Strong direct sun from above, sand-bounce fill on the shadow side.',
                'golden-hour' => 'Golden rim light from the horizon, warm reflected light from wet sand below.',
                'night' => 'Warm market string lights overhead, contained and intimate.',
            ],
            'rooftop' => [
                'morning' => 'Cool blue morning sky as ambient fill, thin first sun from one side.',
                'afternoon' => 'Full direct sun, hard shadows, open sky as fill.',
                'golden-hour' => 'Low sun backlighting from the city horizon, cool sky-bounce on the face.',
                'night' => 'City glow as soft ambient, string lights or LED overhead as warm key.',
            ],
            'bedroom' => [
                'morning' => 'Soft directional window light, single source, clean and warm.',
                'afternoon' => 'Window daylight from one side, cool and clean.',
                'golden-hour' => 'Narrow amber light slanting through the window, high contrast.',
                'night' => 'Bedside lamp as warm tungsten key, intimate falloff.',
            ],
            'bathroom' => [
                'morning' => 'Overhead bathroom light plus soft side window daylight, two temperatures.',
                'afternoon' => 'Frosted window as soft side key, overhead light supplementing.',
                'golden-hour' => 'Overhead bathroom light primary, small warm window accent.',
                'night' => 'Bright overhead bathroom light, contained and even.',
            ],
            'mall' => [
                'morning' => 'Soft cool skylight overhead, warm storefront spill from the sides.',
                'afternoon' => 'Bright skylight from above, warm store lighting from the sides.',
                'golden-hour' => 'Artificial pendants taking over as skylight fades, warm transitional.',
                'night' => 'Warm mall pendants overhead, cool-neutral LED from store entries.',
            ],
            'gym' => [
                'morning' => 'Cool fluorescent overhead, first daylight from a side window.',
                'afternoon' => 'Overhead fluorescent, bright and flat, mirror bounce.',
                'golden-hour' => 'Warm window flood from one side, cool fluorescent overhead.',
                'night' => 'Overhead fluorescent, even and institutional, no natural fill.',
            ],
            'park' => [
                'morning' => 'Cool dappled light through leaf canopy, soft and slightly green-tinted.',
                'afternoon' => 'Dappled sun patches through canopy, warm and cool mixing.',
                'golden-hour' => 'Low warm backlight through leaves, sky-bounce fill on the face.',
                'night' => 'Warm lamp posts creating pools, cool dark gaps between.',
            ],
            'restaurant' => [
                'morning' => 'Bright garden-facing window, clean natural key light.',
                'afternoon' => 'Warm pendant interior, window daylight from one side.',
                'golden-hour' => 'Golden-hour window table, warm outdoor light mixing with warm interior.',
                'night' => 'Candlelight key from below, warm restaurant pendants overhead.',
            ],
            'hotel' => [
                'morning' => 'Strip of morning daylight through the curtains, warm bedside lamp as fill.',
                'afternoon' => 'Floor-to-ceiling window, bright clean directional daylight.',
                'golden-hour' => 'Golden-hour flooding floor-to-ceiling glass, warm and rich.',
                'night' => 'Floor lamp as warm key, city glow through window as ambient.',
            ],
            'studio' => [
                'morning' => 'Large north-facing window, soft cool diffuse daylight from one side.',
                'afternoon' => 'West window directional daylight, studio walls bouncing a soft fill.',
                'golden-hour' => 'Golden-hour flooding from the west window, warm and directional.',
                'night' => 'Two softboxes — primary right at 3 o\'clock, fill left at 9 o\'clock.',
            ],
        ];
        $light = $shortLighting[$location][$timeOfDay] ?? '';

        // ── Camera + framing ───
        $cameraFeel = 'Eye-level, 24mm, handheld.';
        if ($vibe === 'editorial') {
            $cameraFeel = 'Eye-level, 50mm lens feel.';
        } elseif ($vibe === 'luxury') {
            $cameraFeel = 'Eye-level, 28mm, clinical sharpness.';
        }

        $framing = '';
        if ($isSitting) {
            $framing = $aspectRatio === '16:9' ? '16:9, waist-up.' : '9:16, 3/4 framing head to mid-thigh.';
        } else {
            $framing = $aspectRatio === '16:9' ? '16:9, waist-up framing.' : '9:16, chest-up framing.';
        }

        // ── Hairstyle override — beats any reference image ───
        $hairstyleDesc = '';
        $trimmedHair = trim($hairstyleText ?? '');
        if ($hasHairstyleOverride) {
            $hairstyleDesc = "Hairstyle: {$trimmedHair} — apply this hairstyle exactly, overriding the hairstyle shown in any reference image.";
        }

        $opener = '';
        if ($poseTag) {
            $opener = "Modify the base image {$poseTag} to show a {$shotType} of {$subject}, wearing {$wardrobe}. Keep the exact body pose, stance, and camera composition from {$poseTag}, but replace the person in {$poseTag} with {$subject}.";
        } else {
            $opener = "{$shotType} of {$subject}, wearing {$wardrobe}.";
        }

        // ── Assemble into tight paragraph ───
        $parts = [
            $opener,
            $hairstyleDesc,
            $closeUpLine,
            $poseDesc,
            $expressionDesc,
            $gazeDesc,
            $propDesc,
            implode(' ', array_filter([$locationLabel, $scene, $timeAtmo, $light])),
            "{$cameraFeel} {$framing}",
            "Deep focus, no bokeh, photorealistic. {$peopleLine}",
        ];

        return implode(' ', array_filter(array_map('trim', $parts)));
    }

    /**
     * Log generated prompts if app debugging is enabled.
     */
    public static function logPrompt(string $prompt, ?string $basePrompt = null, string $label = 'Image Generation'): void
    {
        $appDebug = config('app.debug');
        $logLevel = config('logging.level', 'debug');

        if ($appDebug && $logLevel === 'debug') {
            if ($basePrompt !== null) {
                Log::debug("[{$label}] Base Prompt:\n{$basePrompt}");
                Log::debug("[{$label}] Enhanced Prompt:\n{$prompt}");
            } else {
                Log::debug("[{$label}] Prompt:\n{$prompt}");
            }
        }
    }
}
