<?php namespace davestewart\sketchpad\help\docs;

use davestewart\sketchpad\config\SketchpadConfig;
use Illuminate\Translation\Translator;
use Illuminate\Http\Request;

/**
 * Use various techniques and helpers to print and format text, html and data
 *
 * @package App\Http\Controllers
 */
class OutputController
{

	public function index()
	{
		md(__DIR__ . '/output.md');
	}

	/**
	 * No need to return data or views; just `echo` directly to the page
	 *
	 * @label echo
	 * @group Text
	 */
	public function text()
	{
		echo 'Hello there';
	}

	/**
	 * Use `p()` to print HTML paragraphs tags
	 */
	public function paragraph()
	{
		?>
		<p>The format of the method is:</p>
<pre class="code php">p($text, $class);</code></pre>
		<p>You can print <strong>normal</strong>, <strong>note</strong> and <strong>custom</strong>-classed paragraphs:</p>
		<div style="margin-left: 25px">

		<?php
			p('I am normal');
			p('I am a note; I passed a boolean true as my 2nd argument', true);
			p('I am custom; I passed the string "special" as my 2nd argument', 'special');
			?>
		</div>
		<?php
		p('See the <a href="../basics/userassets">user assets</a> section on how to customise the supplied styles.');
	}

	/**
	 * Use `alert()` to print Bootstrap "alert" message boxes to the page
	 */
	public function alert()
	{
		p('Pass text only to output a basic Bootstrap "info" alert box:');
		alert('Just text passed');

		p('Pass a 2nd argument of a Bootstrap <a href="http://getbootstrap.com/components/#alerts" target="_blank">alert</a> message class:');
		alert('Passed with "success"', 'success');
		alert('Passed with "warning"', 'warning');
		alert('Passed with "danger"', 'danger');

		p('Pass a 2nd argument of a boolean state to render tick or cross icons:');
		alert('Passed with true', true);
		alert('Passed with false', false);

		p('Pass a 3rd argument of a string to render a custom <a href="http://fontawesome.io/icons/" target="_blank">Font Awesome</a> icon:');
		alert('Passed with "info" and "info"', 'info', 'info');
		alert('Passed with "warning" and "bolt"', 'warning', 'bolt');
	}

	/**
	 * Sketchpad intercepts normal HTML page links, so you can link to other methods
	 *
	 * @group HTML
	 */
	public function links()
	{
		?>
		<p>Relative paths run other methods:</p>
		<ul>
			<li>This links to the <a href="forms">forms</a> method in the same controller</li>
			<li>This links to one of the <a href="../../demo/tools/dumpapp">sample tools</a> in the tools controller</li>
		</ul>

		<p>Use the special <code>sketchpad:</code> protocol to link directly to methods:</p>
		<ul>
			<li>This links to the same <a href="sketchpad:help/demo/tools/dumpapp">sample tool</a> in the tools controller</li>
		</ul>

		<p>Absolute paths (outside of sketchpad) run as normal:</p>
		<ul>
			<li>This loads the <a href="/">main app</a></li>
		</ul>

		<p>External links run as normal:</p>
		<ul>
			<li>This links to <a href="http://google.com" target="_blank">Google</a> in a new window</li>
			<li>This runs some <a href="javascript:alert('Sketchpad rocks!')">JavaScript</a></li>
		</ul>

		<?php
	}

	/**
	 * Sketchpad makes using forms easy, by intercepting and `POST`ing action-less forms on your behalf
	 */
	public function forms(Request $request)
	{
		?>

		<p>Type something below and submit the form back to the same URL:</p>
		<!-- any form with an empty or missing "action" attribute will be intercepted and submitted by sketchpad -->
		<form class="form form-inline">
			<input  class="form-control input-sm" type="text" name="text" placeholder="Type something here...">
			<button class="btn btn-primary btn-sm" type="submit">Submit</button>
		</form>
		<?php

		if($request->isMethod('post'))
		{
			echo '<hr />';
			p('All you need to do is check for a POST submission in the same method, and take action appropriately:');
			dump($request->all());
		}
	}

	/**
	 * Use `vd()`, `pr()` and `pd()` to output object structures with HTML `pre` tag. All functions take variadic parameters
	 *
	 * @label print_r
	 * @group Data
	 */
	public function print_r()
	{
		p('Use <code>pr()</code> to format and <code>print_r()</code>:');
		pr($this->data());

		p('Use <code>vd()</code> to format and <code>var_dump()</code>:');
		vd($this->data());
	}

	/**
	 * Use `dump()` and `dd()` to format data in an interactive tree
	 */
	public function dump()
	{

		p('Use <code>dump()</code> to format and dump:');
		dump($this->data());
		p('And <code>dd()</code> to format and dump and die:');
		dd(app());
	}

	/**
	 * Output objects as JSON and have Sketchpad render them interactively
	 */
	public function json()
	{
		p('Use <code>json()</code> to output objects inline as JSON:');
		json($this->data());
		p('Alternatively, you can simply <i>return</i> any complex object, and Sketchpad will format it for you.');
	}

	/**
	 * Use `ls()` to output any Object or Array in list format (single `foreach` loop)
	 *
	 * @label list
	 * @param string $options
	 */
	public function ls($options = '')
	{
		p('This is the validation config array, formatted as a list:');
		$data   = \App::make(Translator::class)->get('validation');
		ls($data, $options);
		p('Use the same options as the <a href="table">table</a> function.');
	}

	/**
	 * Use `tb()` to output any Collection or Array of Objects in table format (nested `foreach` loop)
	 *
	 * @param string $options
	 */
	public function table($options = 'html:example')
	{

		$rows =
		[
			["option","description","example"],
			["index","Adds a numeric index column to the table","index"],
			["pre","Preformats the entire table, or selected columns","pre, pre:example"],
			["html","Specifies which columns to output as HTML","html:example|html:description,example"],
			["label","Adds a label to the table","label:Formatting options"],
			["width","Sets the width of the table","width:100%"],
			["cols","Sets the width of individual columns","cols:50,400,200|cols:10%,60%,30%"],
			["class","Sets the table class attribute","class:fancy"],
			["style","Sets the table style attribute","style:border:1px solid red; background:blue"]
		];

		$keys   = array_shift($rows);
		$data   = array_map(function($values) use ($keys){
			return array_combine($keys, $values);
		}, $rows);

		foreach ($data as $index => $value)
		{
			$example = $data[$index]['example'];
			$data[$index]['example'] = implode(' ', array_map(function($value){ return "<code>$value</code>";}, explode('|', $example)));
		}

		?>
		<p>This function takes an optional formatting string, with a syntax similar to Laravel validation:</p>
		<pre class="code php">tb($data, '<?php echo $options; ?>');</pre>
		<p>The following table outlines the available options:</p>

		<?php
		tb($data, $options);
?>

		<p>Within the options string, you can:</p>
		<ul>
			<li>add multiple options, using the pipe character</li>
			<li>specify arguments, using a colon</li>
			<li>add multiple arguments, separated with commas</li>
		</ul>

		<p>Experiment with updating the options string above, or click the links below to explore some presets:</p>
<ul>
	<li><a href="?options=html:example|index">Add an index</a></li>
	<li><a href="?options=html:example|index|cols:100,400,300">Set column widths</a></li>
	<li><a href="?options=html:example|index|cols:100,400,300|index|style:background:white;z-index:1000;transform:rotate(10deg)">Set the style</a></li>
	<li><a href="?options=html:example|label:Table formatting options">Set a table caption</a></li>
	<li><a href="?options=html:example">Reset the table</a></li>
	<li><a href="?options=">Clear all settings</a></li>
</ul>
<?php
	}

	/**
	 * Use the `sketchpad::` view shortcut to load sketchpad-specific blade views
	 *
	 * @group File
	 */
	public function blade(SketchpadConfig $config)
	{
		return view('sketchpad::help/blade', ['views' => $config->views]);
	}

	/**
	 * Use `md()` to load markdown `.md` documents from your views folder, which will be rendered client-side
	 */
	public function markdown()
	{
		p('Pass absolute paths or a <code>sketchpad::path.to.view</code> reference.');
		md('sketchpad::help.md.text');
		p('Here is some general markdown formatting:');
		md('sketchpad::help.md.formatting');
		echo '<style>.markdown { margin:25px; }</style>';
	}

	/**
	 * Use `vue()` to load Vue `.vue` templates from your views folder, and even pass data from PHP.
	 */
	public function vue($name = 'World')
	{
		vue('sketchpad::help.vue.form', ['name' => $name]);
	}

	protected function data()
	{
		return [
			'number'    => 1,
			'boolean'   => true,
			'string'    => 'Sketchpad',
			'array'     => [1, 2, 3],
			'object'    => (object) ['a' => 1, 'b' => 2, 'c' => 3],
		];
		
	}
}