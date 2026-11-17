@if ($show)
@if ($scriptOnly)
<script async src="{{ $base }}/w/tyn.js"></script>
@else
<div data-tyn data-key="{{ $siteKey }}" data-form="{{ $form }}"></div>
@endif
@endif
