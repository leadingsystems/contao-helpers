# Leading Systems Contao Helpers bundle

This helpers bundle is meant to assist other Leading Systems extensions such as the
e-commerce extension Merconis. Standalone usage is not recommended.

For more information visit the [Merconis website](https://merconis.com)

## Performance instrumentation helpers

This bundle provides two simple helpers to measure execution time of arbitrary code sections across a user session:

- `performanceCheck($key, $startStop = 'start', $description = '')`
- `performanceCheckResults()`

### What they do

- `performanceCheck` lets you mark the beginning and end of a measured section identified by a custom `$key`.
- Time is accumulated per `$key` between matching `start`/`stop` calls. Multiple cycles per key are supported and aggregated.
- `performanceCheckResults` logs the aggregated results and clears the collected data for the current session.
- Logging is done via `lsDebugLog` to the Symfony/Contao logs directory (`kernel.logs_dir`, fallback `var/logs`) into `lsDebugLog.log`.

### When to use

- Use these helpers for quick, low-overhead timing inside Merconis/Contao code, e.g. identifying hotspots in `ls_shop_product.php` or similar.
- They are session-based and survive across page loads until you call `performanceCheckResults()`.

### Limitations

- They measure wall-clock time only (via `microtime(true)`), not CPU time or memory.
- They do not build a nested call tree; use distinct `$key` names for different sections.
- Results are written to logs, not returned; call `performanceCheckResults()` to flush and reset the counters.

### API

```php
use function LeadingSystems\Helpers\performanceCheck;
use function LeadingSystems\Helpers\performanceCheckResults;

// Start measuring a section
performanceCheck('product.getData', 'start', 'Load product data');

// ... your code ...

// Stop measuring the same section
performanceCheck('product.getData', 'stop');

// Later, dump and reset all results for the current session
performanceCheckResults();
```

Notes:
- `$key` (string) identifies the measured section. Reuse the same key for matching `start`/`stop`.
- `$description` is stored on first use and printed in the log next to the timing.
- Multiple `start`/`stop` pairs per key are supported and aggregated; call `performanceCheckResults()` to log and clear.

### Example: Instrumenting parts of ls_shop_product.php

You can temporarily instrument expensive regions inside `vendor/leadingsystems/contao-merconis/src/Resources/contao/classes/ls_shop_product.php` to find hotspots:

```php
use function LeadingSystems\Helpers\performanceCheck;

// Example around data loading
performanceCheck('product.ls_getData', 'start', 'Load product DB and language data');
$this->ls_getData();
performanceCheck('product.ls_getData', 'stop');

// Example around variants loading
performanceCheck('product.ls_getVariants', 'start', 'Load variants list');
$this->ls_getVariants();
performanceCheck('product.ls_getVariants', 'stop');
```

At the end of a request (e.g. in a controller, a debug-only hook, or temporarily at a suitable exit point), flush the timings:

```php
use function LeadingSystems\Helpers\performanceCheckResults;
performanceCheckResults();
```

### Reading results

After calling `performanceCheckResults()`, check your application logs directory (by default `var/logs`) for `lsDebugLog.log`. Each entry contains:

- The key and description: `Performance check (<key> [starts: <n>, stops: <n>]): <description>`
- The accumulated time (seconds) is appended to the title line.

Example log line (formatted):

```
[123] LeadingSystems\Helpers\performanceCheckResults: LINE 231 VAR: $var_variableOrString - 0.054321
Performance check (product.ls_getData [starts: 1, stops: 1]): Load product DB and language data
```

### Tips for effective usage

- Use consistent, hierarchical-like keys (e.g. `product.load`, `product.variants.fetch`, `product.prices.compute`).
- Keep instrumentation behind a feature flag and remove it after analysis.
- Because timings are stored in the session, you can accumulate across multiple page loads before dumping results.
- Ensure every `start` has a corresponding `stop` for the same key.

### PerfGuard (RAII helper for automatic start/stop)

PerfGuard simplifies instrumentation by starting the timer in its constructor and stopping it in its destructor. This guarantees the stop call even on early returns or exceptions.

#### What it is

- Class: `LeadingSystems\Helpers\PerfGuard`
- Constructor: `new PerfGuard(string $key, string $description = '')` → calls `performanceCheck($key, 'start', $description)`
- Destructor: `~PerfGuard()` → calls `performanceCheck($key, 'stop')`

#### Why it’s reliable

- In PHP, a local object’s destructor runs when it goes out of scope (when the method returns), so the stop call happens automatically for all exit paths.

#### Recommended usage

- Create a guard at the very top of the method and pass `__METHOD__` so the key is fully qualified and refactor-proof:

```php
use LeadingSystems\Helpers\PerfGuard;

public function someMethod(...)
{
    $___pg = new PerfGuard(__METHOD__); // optional second param for a human-readable description
    // ... method body ...
}
```

- If you need to time a smaller block, you can create a nested guard. Consider adding a suffix to the key if you want separate aggregation:

```php
{
    $___pgBlock = new PerfGuard(__METHOD__ . '#db-query', 'Main query');
    // run query
}
```

#### Notes and caveats

- Ensure the guard is instantiated before any early return in the method.
- Use `__METHOD__` (recommended) instead of a hardcoded string; it automatically yields keys like `Vendor\\Package\\Class::method`.
- As with manual calls, results are only logged when you call `performanceCheckResults()` at the end of a request.

