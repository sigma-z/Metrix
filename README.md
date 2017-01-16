# SigmaZ\Metrix

Fetches metrics from pdepend summary.xml.

## Requires
 * PHP 5.3 or greater

[On GitHub]: https://github.com/sigma-z/Metrix


## Usage

Fetching lines of code from pdepend's summary.xml

```php
$metric = new CodeMetric('package[].class[].@attributes.loc');
$metric->addCondition(new Condition(1000, '>'))
    ->setSortInfo('loc', CodeMetric::SORT_DESC) // sorted descending
    ->setResultLimit(20);                       // result is limited to the maximum of 20 classes

$pDependSummary = new pDependSummary();
$pDependSummary->loadFromFile('data/summary.xml');
// classes with more than 1000 lines of code, sorted descended, limited to 20 entries
$result = $pDependSummary->fetchMetric($metric);
var_dump($result);
```

Output example:

    array(20) {
        [0]=>
        array(3) {
            ["package"]=>
            string(13) "SamplePackage"
            ["class"]=>
            string(11) "SampleClass"
            ["loc"]=>
            string(11) "1234"
        }
        .. more entries ..
    }
    

Fetching lines of code of a class from pdepend's summary.xml

```php
$metric = new CodeMetric('package[].class[].@attributes[name=SampleClass].loc');
$pDependSummary = new pDependSummary();
$pDependSummary->loadFromFile('data/summary.xml');
$result = $pDependSummary->fetchMetric($metric);
var_dump($result);
```

Output example:

    array(1) {
      [0] =>
      array(1) {
        'loc' =>
        string(4) "1234"
      }
    }

