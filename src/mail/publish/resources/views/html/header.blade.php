@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel Hyperf')
<img src="https://laravel-hyperf.com/icon.png" class="logo" alt="Laravel Hyperf Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>