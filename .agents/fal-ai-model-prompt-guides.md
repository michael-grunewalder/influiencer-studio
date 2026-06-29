# FAL.AI Model Prompt Guides
### For AI Influencer Image Generation

> These guides are sourced directly from official FAL.AI documentation and prompt engineering resources. Use them to build your `MODEL_GUIDES` config and dynamically construct the `ALWAYS INCLUDE` section of your Claude system prompt based on the selected model.

---

## How to Use This Document

Each model below includes:
- **Prompt Format** — the required structure the model expects
- **Prompt Style** — how the model "reads" prompts (natural language vs. keywords)
- **Always Include** — the specific fields to inject into your Claude system prompt
- **FAL.AI Endpoint** — the model string for your API calls
- **Example Prompt** — a reference output Claude should aim to produce

---

## 1. GPT Image 1.5

**FAL Endpoint:** `fal-ai/gpt-image-1.5`

### Prompt Format
```
Subject + Location + Action + Lighting + Style + Constraints
```

### Prompt Style
Natural language. Conversational and descriptive. The model understands context, spatial relationships, and historical/environmental cues. Avoid vague praise words like "stunning" or "masterpiece" — replace them with concrete visual facts.

### Always Include
1. **Subject** — who is in the image, physical appearance, outfit, expression
2. **Location** — specific environment with atmosphere, time of day, background
3. **Action** — what the subject is doing or how they are positioned
4. **Lighting** — type, source direction, intensity, color temperature
5. **Style** — photographic approach (editorial, lifestyle, candid, luxury brand)
6. **Constraints** — what must NOT appear (no watermark, no extra people, no logos)
7. **Quality** — output format note (`high` quality, `PNG` for design work)

### Example Prompt
```
Scene: A sunlit rooftop terrace in Tokyo at golden hour.
Subject: A confident woman in her mid-20s wearing a white linen co-ord set, hair in a loose bun.
Action: Leaning against the railing, looking over the city with a relaxed smile.
Lighting: Warm directional sunlight from the left, soft lens flare, golden hour glow.
Style: Fashion editorial, authentic lifestyle photography.
Constraints: No watermark, no additional people, no logos.
```

---

## 2. GPT Image 2

**FAL Endpoint:** `openai/gpt-image-2`

### Prompt Format
```
Scene + Subject + Important Details + Use Case + Constraints
```

### Prompt Style
Structured with **labeled sections and line breaks**. The model rewards explicit structure. Use the five-slot template below — each slot solves a specific problem the model needs to resolve before rendering. Describe the photograph, not the fantasy.

### Always Include
1. **Scene** — where the image exists (location, time of day, background, environment)
2. **Subject** — who or what is the main focus (identity, clothing, expression, pose)
3. **Important Details** — materials, skin texture, lighting setup, camera angle, lens feel, composition, mood, color balance
4. **Use Case** — what kind of finished image this is (editorial photo, product mockup, social content, poster)
5. **Constraints** — what must not drift (no watermark, preserve face, no extra text, no logo)

### Example Prompt
```
Scene:
A minimalist luxury hotel lobby in Seoul, warm evening light, marble floors.

Subject:
A woman in her late 20s in a tailored black blazer and gold earrings, standing confidently.

Important Details:
Natural smile, realistic skin texture, center-framed, eye-level full-body shot,
warm neutral color balance, shallow depth of field, marble floor reflections.

Use case:
Fashion editorial for Instagram feed.

Constraints:
No watermark, no extra people, no text overlays.
```

---

## 3. Ideogram 4

**FAL Endpoint:** `fal-ai/ideogram/v3` *(Ideogram 4 — latest open-weight release)*

### Prompt Format
```
Subject + Style + Composition + Lighting + Typography (if needed) + Color Palette
```

Ideogram 4 also supports **JSON structured prompting** for precise bounding-box layout control — use this for designs that require specific text placement.

### Prompt Style
Natural language with **explicit typography instructions**. This is the #1 model for text-in-image accuracy. Put any text that must appear in the image inside **quotation marks** and name the font style explicitly. For design work, describe the layout as if briefing a graphic designer. Ideogram 4 was trained on structured JSON captions, so describing visual relationships clearly is important.

### Always Include
1. **Subject** — the influencer, their appearance, outfit, pose
2. **Style** — visual aesthetic reference (editorial, luxury brand, streetwear, etc.)
3. **Composition** — layout of elements, framing, spatial relationships
4. **Lighting** — specify source, direction, and quality ("single soft key light from upper left")
5. **Typography** — any text that should appear in the image, in quotes, with font style
6. **Color Palette** — use hex values or named colors for brand consistency
7. **Negative Space** — mention "intentional negative space" if you want clean areas for text

### Example Prompt
```
A stylish young woman in streetwear standing in front of a matte black wall.
The wall features bold white sans-serif text reading "FEARLESS".
Dramatic side lighting from the left, deep shadows, high contrast.
Editorial fashion photography style. Dark color palette: black, white, neon pink accents.
Minimal composition, wide-angle environmental shot.
```

---

## 4. Seedream 4.5

**FAL Endpoint:** `fal-ai/bytedance/seedream/v4-5/text-to-image`

### Prompt Format
```
Subject + Style + Composition + Lighting and Atmosphere + Technical Parameters
```

### Prompt Style
Descriptive sentences with **front-loaded subject information**. Seedream 4.5 prioritizes whatever comes first — always open with the subject. Use **30–100 words** for best results (under 15 is too vague, over 150 causes competing instructions). Negative prompts are supported and important for quality control. For text-in-image, quote the exact text and keep it to 3–10 characters.

### Always Include
1. **Subject** — front-loaded description of the influencer (appearance, outfit, expression)
2. **Style** — photographic approach ("editorial photography", "cinematic still", "authentic UGC")
3. **Composition** — shot type and framing ("medium close-up, centered", "environmental wide shot")
4. **Lighting** — specific cue ("golden hour lighting", "dramatic side lighting", "soft studio diffusion")
5. **Technical Parameters** — lens spec ("85mm portrait, f/1.8"), resolution ("4K"), camera brand for color science
6. **Negative Prompt** — always include: `blurry, low resolution, watermark, extra fingers, distorted hands, plastic skin, overexposed`

### Example Prompt
```
Professional woman in her early 30s, shoulder-length brown hair, wearing a camel trench coat,
confident expression, walking down a rain-slicked Parisian street at dusk.
Cinematic editorial photography style. Medium full-body shot, centered composition,
city lights blurred in background. Warm tungsten streetlight from the right,
cool blue ambient from overcast sky. Shot on Canon EOS R5, 85mm lens at f/2.0, 4K quality.
```

**Negative Prompt:**
```
blurry, low resolution, watermark, logo, extra fingers, distorted hands, deformed eyes,
asymmetrical face, plastic skin, oversaturated, overexposed
```

---

## 5. Nano Banana Pro

**FAL Endpoint:** `fal-ai/nano-banana-pro` *(powered by Google Gemini 3 Pro)*

### Prompt Format
```
Subject + Action + Location + Look/Style + Framing + Lighting + On-Image Text
```

### Prompt Style
**Art director brief style** — write it like you're briefing a photographer on a paid shoot. The model *reasons about the scene before it renders*, so it handles complex compositions and spatial logic well. Replace all adjectives with concrete visual facts. Do NOT use "stunning", "beautiful", or "8K" — instead describe the overcast light, the chipped paint, the teal-and-amber grade. Supports up to 14 reference images.

### Always Include
1. **Subject** — who is in frame, physical details, outfit, expression
2. **Action** — what they are doing (posing, walking, looking over shoulder)
3. **Location** — specific environment with environmental detail (not just "a street" — "a rain-slicked Shibuya crossing at midnight")
4. **Look/Style** — photographic or artistic style ("35mm documentary", "Vogue editorial", "authentic UGC lifestyle")
5. **Framing** — camera position and lens ("eye-level shot at 85mm", "wide-angle environmental at 24mm")
6. **Lighting** — concrete lighting setup ("soft overcast light", "rim-lit from behind with a practical neon sign")
7. **On-Image Text** — any text that must appear, quoted exactly, with font style specified
8. **What Must Not Change** — for edits: explicitly lock what should stay the same

### Example Prompt
```
A fashion influencer in her mid-20s with natural curly hair wearing an oversized vintage
band tee tucked into high-waist leather trousers, standing with one hand on a graffiti wall
in Hackney, East London, late afternoon. Authentic street photography style, not posed.
Eye-level shot on 35mm, slight film grain, warm muted grade. Overcast diffused light,
no harsh shadows. Brick wall texture visible, puddles on pavement reflecting neon from a nearby
shop sign. No watermark, no extra people.
```

---

## Quick Reference: Model Selector

| Model | Best For | Prompt Style | Supports Negative Prompt |
|---|---|---|---|
| **GPT Image 1.5** | Contextual realism, environmental scenes | Natural language | No |
| **GPT Image 2** | Photorealism, product shots, text-in-image | Structured sections | No |
| **Ideogram 4** | Typography, graphic design, brand content | Natural + JSON layout | No |
| **Seedream 4.5** | Portraits, fashion, high-speed iteration | Structured sentences | ✅ Yes |
| **Nano Banana Pro** | Character consistency, complex scenes, editorial | Art director brief | No |

---

## Implementation: Dynamic System Prompt Builder

```javascript
const MODEL_GUIDES = {
  "fal-ai/gpt-image-1.5": {
    name: "GPT Image 1.5",
    format: "Subject + Location + Action + Lighting + Style + Constraints",
    alwaysInclude: [
      "Subject: physical appearance, outfit, expression",
      "Location: specific environment, time of day, atmosphere",
      "Action: what the subject is doing or how they are positioned",
      "Lighting: type, source direction, intensity, color temperature",
      "Style: editorial, lifestyle, candid, luxury brand aesthetic",
      "Constraints: no watermark, no logos, no extra people",
    ],
    supportsNegativePrompt: false,
  },
  "openai/gpt-image-2": {
    name: "GPT Image 2",
    format: "Scene + Subject + Important Details + Use Case + Constraints",
    alwaysInclude: [
      "Scene: where the image exists (location, time of day, background)",
      "Subject: who is the main focus (identity, clothing, expression, pose)",
      "Important Details: materials, skin texture, camera angle, lens feel, mood",
      "Use Case: editorial photo / product mockup / social content / poster",
      "Constraints: no watermark, preserve face, no extra text",
    ],
    supportsNegativePrompt: false,
  },
  "fal-ai/ideogram/v3": {
    name: "Ideogram 4",
    format: "Subject + Style + Composition + Lighting + Typography + Color Palette",
    alwaysInclude: [
      "Subject: influencer appearance, outfit, pose",
      "Style: visual aesthetic reference (editorial, luxury, streetwear)",
      "Composition: layout, framing, spatial relationships",
      "Lighting: source, direction, quality ('single soft key light from upper left')",
      "Typography: any text in quotes with font style specified",
      "Color Palette: hex values or named colors",
    ],
    supportsNegativePrompt: false,
  },
  "fal-ai/bytedance/seedream/v4-5/text-to-image": {
    name: "Seedream 4.5",
    format: "Subject + Style + Composition + Lighting + Technical Parameters",
    alwaysInclude: [
      "Subject: front-loaded appearance, outfit, expression (MUST come first)",
      "Style: photographic approach ('editorial photography', 'cinematic still')",
      "Composition: shot type and framing ('medium close-up, centered')",
      "Lighting: specific cue ('golden hour', 'dramatic side lighting', 'studio diffusion')",
      "Technical Parameters: lens (85mm, f/1.8), resolution (4K), camera brand",
    ],
    supportsNegativePrompt: true,
    defaultNegativePrompt:
      "blurry, low resolution, watermark, logo, extra fingers, distorted hands, plastic skin, overexposed",
  },
  "fal-ai/nano-banana-pro": {
    name: "Nano Banana Pro",
    format: "Subject + Action + Location + Look/Style + Framing + Lighting + On-Image Text",
    alwaysInclude: [
      "Subject: who is in frame — physical details, outfit, expression",
      "Action: what they are doing (posing, walking, looking over shoulder)",
      "Location: specific environment with concrete detail (not 'a street' — 'rain-slicked Shibuya at midnight')",
      "Look/Style: '35mm documentary', 'Vogue editorial', 'authentic UGC lifestyle'",
      "Framing: camera position and lens ('eye-level at 85mm', 'wide-angle at 24mm')",
      "Lighting: concrete setup ('soft overcast light', 'rim-lit by a neon sign behind')",
      "On-Image Text: quoted exactly with font style if needed",
    ],
    supportsNegativePrompt: false,
  },
};

function buildSystemPrompt(modelKey) {
  const guide = MODEL_GUIDES[modelKey];
  const includeList = guide.alwaysInclude
    .map((item, i) => `${i + 1}. ${item}`)
    .join("\n");

  return `You are an elite AI image prompt engineer for AI influencer content on FAL.AI.
You are generating prompts specifically for the **${guide.name}** model.

## Model Format Rule — Always Follow This Structure
**${guide.format}**

## Always Include (in the correct format order above)
${includeList}
${
  guide.supportsNegativePrompt
    ? `\n## Negative Prompt\nAlways generate a negative prompt. Default base:\n"${guide.defaultNegativePrompt}"\n`
    : ""
}
## Anti-Slop Rules
- Never use: "stunning", "beautiful", "masterpiece", "breathtaking", "8K ultra HD"
- Replace ALL adjectives with concrete visual facts
- Wrong: "beautiful lighting" → Right: "soft diffused light from a north-facing window"
- Wrong: "stunning outfit" → Right: "oversized camel trench coat with gold buttons, collar up"

## Output Format
Return ONLY this JSON — no markdown, no explanation:
{
  "enhanced_prompt": "prompt built in ${guide.name} format",
  "negative_prompt": "${guide.supportsNegativePrompt ? "negative prompt string" : "null"}",
  "platform": "Instagram | TikTok | Pinterest | LinkedIn",
  "style_tags": ["tag1", "tag2", "tag3"],
  "mood": "one-word mood descriptor"
}`;
}
```

---

*Sources: [FAL.AI Learn Hub](https://fal.ai/learn) — GPT Image 1.5 Prompt Guide, GPT Image 2 Prompting Guide, Ideogram 4 on FAL, Seedream v4.5 Prompt Guide, Nano Banana Pro Prompting Guide. Verified June 2026.*
