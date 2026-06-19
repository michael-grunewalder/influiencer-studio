<?php

namespace App\Services;

use App\Data\InfluencerProperties;
use Illuminate\Database\Eloquent\Model;

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

        $physicalDesc = $propsArray['physical_description'] ?? $propsArray['physicalDesc'] ?? '';
        if (empty($physicalDesc)) {
            $physicalDesc = self::buildPhysicalDescString($propsArray);
        }

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
            $ctx = "Character background: {$slicedBackstory}. Let this inform their physique, presence, and energy — e.g. a personal trainer should look visibly athletic and fit, a gamer may look relaxed and casual, a CEO projects confidence. ";
        }

        return "Professional full-body character turnaround sheet. Pure white background, no background elements whatsoever. Soft neutral studio lighting, perfectly flat and even across all four panels — no shadows, no color cast, no vignette.

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
}
