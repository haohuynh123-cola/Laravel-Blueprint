# vhs script — renders docs/demo.gif from the wizard interaction.
#
# Run with:
#   vhs docs/demo.tape
#
# The recording captures the laravel/prompts wizard end-to-end then aborts
# with Ctrl+C right when the generator phase begins — so the GIF stays
# under ~30 seconds and shows the value (the wizard) without waiting
# minutes for `composer create-project laravel/laravel`.

Output docs/demo.gif

# Visual setup — chosen for legibility when the GIF is embedded at half-size
# in a README or social timeline. Bigger font, generous padding, high-contrast
# theme.
Set Theme "Catppuccin Mocha"
Set FontFamily "JetBrains Mono"
Set FontSize 22
Set Width 1280
Set Height 820
Set Padding 36
Set Margin 0
Set TypingSpeed 40ms
Set Framerate 30
Set PlaybackSpeed 1.0

Hide
# `Hide`/`Show` lets us prep the prompt without polluting the GIF.
Type "rm -rf /tmp/blueprint-demo && mkdir -p /tmp/blueprint-demo && cd /tmp/blueprint-demo && clear"
Enter
Sleep 400ms
Show

# 1. Project name
Type "blueprint new"
Sleep 600ms
Enter
Sleep 1200ms

Type "demo-app"
Sleep 1100ms
Enter
Sleep 1100ms

# 2. Starter kit — Down to Breeze
Down
Sleep 400ms
Enter
Sleep 1100ms

# 3. Frontend stack — Down twice to Inertia + Vue
Down
Sleep 250ms
Down
Sleep 400ms
Enter
Sleep 1100ms

# 4. Database — Down twice to PostgreSQL
Down
Sleep 250ms
Down
Sleep 400ms
Enter
Sleep 1100ms

# 5. Cache driver — Down twice to Redis
Down
Sleep 250ms
Down
Sleep 400ms
Enter
Sleep 1100ms

# 6. Queue driver — pick RabbitMQ (last item, several Downs)
Down
Sleep 200ms
Down
Sleep 200ms
Down
Sleep 200ms
Down
Sleep 200ms
Down
Sleep 400ms
Enter
Sleep 1100ms

# 7. Test runner (default Pest)
Sleep 600ms
Enter
Sleep 1100ms

# 8. Extras — defaults already include Pint + Larastan; add Horizon
Down
Sleep 200ms
Down
Sleep 200ms
Space
Sleep 1100ms
Enter
Sleep 1100ms

# 9. Docker — Down twice to Production
Down
Sleep 200ms
Down
Sleep 400ms
Enter
Sleep 1100ms

# 10. CI — Down to GitHub Actions
Down
Sleep 400ms
Enter
Sleep 1100ms

# 11. Git — default is commit; just confirm
Sleep 600ms
Enter

# Capture the start of the generator phase, then abort.
Sleep 3500ms
Ctrl+C
Sleep 1100ms
