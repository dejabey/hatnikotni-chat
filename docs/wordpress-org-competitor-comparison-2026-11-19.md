# WhatsApp plugin comparison for the WordPress.org reply (2026-11-19)

Method: each plugin's own WordPress.org page (description, FAQ, changelog), read on 2026-11-19. A feature "not described" means the page does not describe it. That is **not** proof the plugin lacks it, so the reply must say "I could not find", never "no other plugin".

| Plugin (slug) | Installs | Several numbers in the free version | Automatic distribution across numbers | Click analytics | Consent for analytics | UTM attribution |
|---|---|---|---|---|---|---|
| Joinchat (`creame-whatsapp-me`) | 700,000+ | Multi-agent is Premium | Not described | Dashboard click counter; events to GA4/GTM/Meta Pixel, Google Ads | Cookieless mode; no consent-gated analytics described | Not described (page URL can go into the message) |
| Click to Chat (`click-to-chat-for-whatsapp`) | 600,000+ | Multi-agent is PRO; per-page number override | Random number is PRO; round-robin not described | Events to GA, Meta Pixel, Google Ads | Opt-in before chat (about contact, not tracking) | PRO: URL parameters such as `utm_source` sent to GA/Pixel |
| Social Chat (`wp-whatsapp-chat`) | 200,000+ | Premium | Not described | GA events are Premium | GDPR checkbox is Premium | Not described |
| Chaty (`chaty`) | 300,000+ | Can duplicate a channel; Chat Agents are Pro | Not described | Widget analytics and GA are Pro | Works with cookie-consent plugins | Not described |
| WP Chat App (`wp-whatsapp`) | 100,000+ | Yes (agents in one box) | Visitor or admin picks an agent; automatic method not described | GA/GTM/Meta Pixel are PRO | Terms checkbox before messaging | Not described |

## What Hatnikotni Chat can claim, carefully
- **Free, automatic distribution of enquiries across several staff numbers** (round robin with a database lock, verified under 12 concurrent requests; a safe fallback contact if rotation fails; also direct and random). None of the five pages describes automatic round-robin.
- **First-party, consent-gated measurement:** clicks are stored only after the visitor accepts, in the site's own database, with UTM campaign attribution. No third-party tracker is involved, and WhatsApp keeps working if the visitor rejects. The five pages describe sending events to Google or Meta, or a simple counter, not consent-gated local attribution.
- **Overlap, stated openly:** WhatsApp buttons, multi-agent boxes, click counting and consent checkboxes all exist elsewhere. The distinct part is the **routing plus consent-aware attribution workflow for small teams**, not any single feature.
