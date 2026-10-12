@extends('layouts.admin')
@section('title', 'Users and roles')
@section('heading', 'Users & roles')
@section('content')
    <section class="section article-paper">
        <h2>Create a staff account</h2>
        <p class="helper-text">New staff accounts are created by a super administrator; passwords must be at least 12 characters.</p>
        <form method="POST" action="{{ route('admin.users.store') }}" class="form-grid">
            @csrf
            <div class="field"><label for="name">Name</label><input id="name" name="name" required maxlength="255"></div>
            <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" required maxlength="255"></div>
            <div class="field"><label for="password">Initial password</label><input id="password" type="password" name="password" required minlength="12" autocomplete="new-password"></div>
            <div class="field"><label for="role">Role</label><select id="role" name="role">@foreach(['administrator','editor','author','reporter'] as $role)<option value="{{ $role }}">{{ ucfirst($role) }}</option>@endforeach</select></div>
            <button class="button-primary">Create staff account</button>
        </form>
    </section>
    <div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Change role</th></tr></thead><tbody>
        @foreach($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ str_replace('_', ' ', ucfirst($user->role)) }}</td><td>
            @if($user->role !== 'super_admin')
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-actions">@csrf @method('PUT')<select name="role" aria-label="New role for {{ $user->name }}">@foreach(['administrator','editor','author','reporter'] as $role)<option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>@endforeach</select><button class="button">Update</button></form>
            @else Super Admin @endif
        </td></tr>@endforeach
    </tbody></table></div>
    <div class="pagination">{{ $users->links() }}</div>
@endsection
