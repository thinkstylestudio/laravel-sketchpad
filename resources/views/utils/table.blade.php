<table class="table table-bordered table-striped debug">
	<colgroup>
		<col class="col-xs-1">
		<col class="col-xs-7">
	</colgroup>
	<thead>
		<tr>
			<th>Key</th>
			<th>Value</th>
		</tr>
	</thead>
	<tbody>
		@foreach($data as $key => $value)
		<tr>
			<th class="key" scope="row">{{ $key }}</th>
			<td class="value {{ $classes }}">{{ is_array($value) || is_object($value) ? print_r($value, true) : $value }}</td>
		</tr>
		@endforeach
	</tbody>
</table>