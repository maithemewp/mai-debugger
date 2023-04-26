# Mai Debugger
An aggressive debugging plugin with Whoops and Symfony var-dumper.

## How It Works
Any PHP errors will loudly display themselves via Whoops automatically.

You can dump data on the screen with `dump( $some_data )` function.

## Disable
You can disable Whoops on a specific page load by adding `?maidebugger=off` query parameter to the URL.

## Deactivate
You can force deactivate the plugin by adding `?maidebugger=deactivate` query paramter to any URL.