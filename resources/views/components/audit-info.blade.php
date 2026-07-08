@props(['model'])

<x-detail-card title="Audit trail" icon="clock-history">
    <x-detail-row label="Created">
        {{ $model->created_at?->format('M d, Y H:i') ?? '—' }}
        @if ($model->creator)
            <span class="text-muted small">by {{ $model->creator->name }}</span>
        @endif
    </x-detail-row>
    <x-detail-row label="Last updated">
        {{ $model->updated_at?->format('M d, Y H:i') ?? '—' }}
        @if ($model->updater)
            <span class="text-muted small">by {{ $model->updater->name }}</span>
        @endif
    </x-detail-row>
    @if ($model->trashed())
        <x-detail-row label="Deleted">
            <span class="text-danger">{{ $model->deleted_at?->format('M d, Y H:i') }}</span>
        </x-detail-row>
    @endif
    <x-detail-row label="Record ID">
        <code class="small">{{ $model->id }}</code>
    </x-detail-row>
</x-detail-card>
