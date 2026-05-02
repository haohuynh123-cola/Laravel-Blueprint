# frozen_string_literal: true

# Homebrew formula for laravel-blueprint.
#
# This file is the canonical source — the workflow in
# .github/workflows/homebrew.yml copies it to the tap repo
# (haohuynh123-cola/homebrew-laravel-blueprint) on every release, with
# `version`, `url`, and `sha256` substituted to point at the new phar.
#
# To install once the tap repo exists:
#
#   brew tap haohuynh123-cola/laravel-blueprint
#   brew install laravel-blueprint

class LaravelBlueprint < Formula
  desc "Scaffold production-ready Laravel projects with one command"
  homepage "https://github.com/haohuynh123-cola/Laravel-Blueprint"
  url "https://github.com/haohuynh123-cola/Laravel-Blueprint/releases/download/__VERSION__/blueprint.phar"
  version "__VERSION_NO_V__"
  sha256 "__SHA256__"
  license "MIT"

  depends_on "php"

  def install
    libexec.install "blueprint.phar"

    (bin/"blueprint").write <<~EOS
      #!/bin/bash
      exec "#{Formula["php"].opt_bin}/php" "#{libexec}/blueprint.phar" "$@"
    EOS

    chmod 0o755, bin/"blueprint"
  end

  test do
    assert_match "Laravel Blueprint", shell_output("#{bin}/blueprint --version")
  end
end
