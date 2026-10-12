@extends('layouts.admin')
@section('title', 'Notices')
@section('heading', 'Notices & opportunities')
@section('content')
    <p style="margin:20px 0"><a class="button-primary" href="{{ route('admin.notices.create') }}">Create notice</a></p>
    <div class="table-wrap"><table><thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Deadline</th><th>Actions</th></tr></thead><tbody>
        @forelse($notices as $notice)<tr><td>{{ $notice->title }}<br><span class="meta">{{ $notice->organization }}</span></td><td>{{ ucfirst($notice->type) }}</td><td>{{ ucfirst($notice->status) }}</td><td>{{ $notice->deadline?->format('M j, Y') ?: '—' }}</td><td><div class="admin-actions"><a class="button" href="{{ route('admin.notices.edit', $notice) }}">Edit</a><form method="POST" action="{{ route('admin.notices.destroy', $notice) }}" onsubmit="return confirm('Delete this notice?')">@csrf @method('DELETE')<button class="button button-danger">Delete</button></form></div></td></tr>
        @empty<tr><td colspan="5">No notices yet.</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination">{{ $notices->links() }}</div>
@endsection
