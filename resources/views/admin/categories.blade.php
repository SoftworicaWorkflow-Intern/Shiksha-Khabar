@extends('layouts.admin')
@section('title', 'Categories')
@section('heading', 'Category management')
@section('content')
    <section class="section article-paper">
        <h2>Create a category</h2>
        <form method="POST" action="{{ route('admin.categories.store') }}" class="form-grid">
            @csrf
            <div class="field"><label for="new-name">Name</label><input id="new-name" name="name" required maxlength="120"></div>
            <div class="field"><label for="new-slug">Slug (optional)</label><input id="new-slug" name="slug" maxlength="160"></div>
            <div class="field"><label for="new-description">Description</label><textarea id="new-description" name="description"></textarea></div>
            <div class="field"><label for="new-parent">Parent category</label><select id="new-parent" name="parent_id"><option value="">None</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
            <div class="field"><label for="new-order">Display order</label><input id="new-order" type="number" name="sort_order" min="0" value="0"></div>
            <label><input type="checkbox" name="is_active" value="1" checked> Visible to readers</label>
            <button class="button-primary" type="submit">Add category</button>
        </form>
    </section>
    <div class="table-wrap"><table><thead><tr><th>Category</th><th>Articles</th><th>Update</th><th>Delete</th></tr></thead><tbody>
        @foreach($categories as $category)
            <tr><td colspan="4">
                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="form-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr));align-items:end">
                    @csrf @method('PUT')
                    <div class="field"><label for="name-{{ $category->id }}">Name</label><input id="name-{{ $category->id }}" name="name" value="{{ $category->name }}" required maxlength="120"></div>
                    <div class="field"><label for="slug-{{ $category->id }}">Slug</label><input id="slug-{{ $category->id }}" name="slug" value="{{ $category->slug }}" maxlength="160"></div>
                    <div class="field"><label for="order-{{ $category->id }}">Order</label><input id="order-{{ $category->id }}" type="number" name="sort_order" value="{{ $category->sort_order }}" min="0"></div>
                    <div class="field"><label for="parent-{{ $category->id }}">Parent</label><select id="parent-{{ $category->id }}" name="parent_id"><option value="">None</option>@foreach($categories->where('id', '!=', $category->id) as $parent)<option value="{{ $parent->id }}" @selected($category->parent_id === $parent->id)>{{ $parent->name }}</option>@endforeach</select></div>
                    <div class="field"><label for="desc-{{ $category->id }}">Description</label><input id="desc-{{ $category->id }}" name="description" value="{{ $category->description }}" maxlength="2000"></div>
                    <label><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Active</label>
                    <span class="meta">{{ $category->articles_count }} stories</span>
                    <button class="button" type="submit">Save</button>
                </form>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="margin-top:8px" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')<button class="button button-danger" type="submit">Delete</button></form>
            </td></tr>
        @endforeach
    </tbody></table></div>
@endsection
