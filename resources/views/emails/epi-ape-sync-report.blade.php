<x-mail::message>
# EPI Auto Price Engine Sync Report

Here is the summary of the latest price synchronization run.

@if(!empty($errors))
<x-mail::panel>
**Errors Encountered:**
@foreach($errors as $error)
- {{ $error }}
@endforeach
</x-mail::panel>
@endif

@if(!empty($updates))
## Price Updates

The following products have been updated:

<x-mail::table>
| Product SKU | Old Price | New Price | Change |
|:--- |:--- |:--- |:--- |
@foreach($updates as $update)
| {{ $update['sku'] }} | {{ number_format($update['old_price']) }} | {{ number_format($update['new_price']) }} | {{ $update['change'] > 0 ? '+' : '' }}{{ number_format($update['change']) }} |
@endforeach
</x-mail::table>
@else
No price changes in this run.
@endif

<x-mail::button :url="route('admin.integrations.epi-ape')">
View Integration Settings
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
