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

# Visual setup
Set Theme "TokyoNight"
Set FontFamily "JetBrains Mono"
Set FontSize 16
Set Width 1100
Set Height 760
Set Padding 24
Set TypingSpeed 45ms

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
Sleep 800ms
Enter
Sleep 800ms

# 2. Starter kit — Down to Breeze
Down
Sleep 400ms
Enter
Sleep 800ms

# 3. Frontend stack — Down twice to Inertia + Vue
Down
Sleep 250ms
Down
Sleep 400ms
Enter
Sleep 800ms

# 4. Database — Down twice to PostgreSQL
Down
Sleep 250ms
Down
Sleep 400ms
Enter
Sleep 800ms

# 5. Cache driver — Down twice to Redis
Down
Sleep 250ms
Down
Sleep 400ms
Enter
Sleep 800ms

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
Sleep 800ms

# 7. Test runner (default Pest)
Sleep 600ms
Enter
Sleep 800ms

# 8. Extras — defaults already include Pint + Larastan; add Horizon
Down
Sleep 200ms
Down
Sleep 200ms
Space
Sleep 800ms
Enter
Sleep 800ms

# 9. Docker — Down twice to Production
Down
Sleep 200ms
Down
Sleep 400ms
Enter
Sleep 800ms

# 10. CI — Down to GitHub Actions
Down
Sleep 400ms
Enter
Sleep 800ms

# 11. Git — default is commit; just confirm
Sleep 600ms
Enter

# Capture the start of the generator phase, then abort.
Sleep 3500ms
Ctrl+C
Sleep 800ms
