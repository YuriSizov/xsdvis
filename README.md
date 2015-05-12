# XSD Visualization Library

If you need to present your scheme description to user base, this just might do the trick.

Build for very specific XSD-parsing needs it has it's uses in general, or can be adapted.

## How to use
Simply do this and you'll get an HTML layout ready to print out:

```php
include_once "visualizer.class.php";
$v = new XSDVis\Visualizer('example/schema.xsd');
echo $v->draw();
```

Add styles, some interactivity, and you're in business. An example is provided on how this can be achieved.

If you don't like the output of Visualizer itself, you sure can make your own class.

Simply call parser directly and use it's results to your desire; check with Visualizer for reference.
```php
try {
  $this->parser = new XSDVis\Parser($filepath);
} catch (Exception $e) {
  echo "Parser - Exception occurred: " . $e->getMessage();
}
$this->parser->parse();
```