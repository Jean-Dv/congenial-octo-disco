<?php

declare(strict_types=1);

namespace Modules\News\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class NewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $newsId = $this->route('news');

        return [
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash', Rule::unique('news', 'slug')->ignore($newsId)],
            'excerpt' => ['required', 'string', 'max:500'],
            'body_markdown' => ['required', 'string'],
            'category_id' => ['required', 'integer', Rule::exists('news_categories', 'id')],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['boolean'],
            'cover' => [$this->isMethod('post') && ! $newsId ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
