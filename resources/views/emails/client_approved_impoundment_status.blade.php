@php
    /** @var \App\Models\Impoundment $impoundment */
@endphp

<p>Dear Team,</p>

<p>Client has approved. Now you can change the status.</p>

<ul>
    <li><strong>Impoundment ID:</strong> {{ $impoundment->id }}</li>
    <li><strong>Current Status:</strong> {{ $currentLabel }}</li>
    <li><strong>Vehicle:</strong> {{ $impoundment->vehicle?->fleet_no }} - {{ $impoundment->vehicle?->truck_no }}</li>
</ul>

<p>Regards,<br/>Fleet Management System</p>

