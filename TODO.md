# TODO

## Consider moving the blocked-request fix into mai-local-isolate

**Status:** open, but no longer urgent. 0.4.0 stops the bleeding here.

On a mai-herd clone, `mai-local-isolate` sets `WP_HTTP_BLOCK_EXTERNAL`. Plugins that call out to their vendor on load then fail, and a lot of them build the error message with `__()`. That runs during `plugins_loaded`, before `init`, so WordPress 6.7+ fires a `_load_textdomain_just_in_time` notice.

0.4.0 handles the consequence: Whoops no longer escalates that notice to a fatal, so the site keeps rendering and the notice still lands in `debug.log`. Hit on hydroplex (2026-08-17) with Download Monitor (`src/Util/ExtensionLoader.php:20`) and WP Migrate DB Pro.

What's left is the cause. Every clone still makes a doomed HTTP request on load, waits for it to fail, caches the failure, and retries when the cache expires. That's slower page loads and log noise on every cloned site, forever.

**The idea:** a filter in mai-local-isolate that answers blocked outbound requests with a valid empty response instead of a `WP_Error`, so the calling plugin takes its success path and never builds an error string at all. Fixes it for sites that don't run mai-debugger too.

**Needs thought before building:**

- Which hosts to answer for. Answering everything is a big behavior change and could mislead plugins in worse ways than a clean failure does.
- Whether a fake 200 with an empty body is safe generally, or whether the shape has to be per-plugin (which doesn't scale any better than the per-site stub did).
- Whether a plugin that caches the fake response for a week then behaves oddly is worse than one that just fails.

A per-site version of this ran on hydroplex as `mu-plugins/dlm-offline-stub.php` and worked, but it was removed once 0.4.0 made it unnecessary.
