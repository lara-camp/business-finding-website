<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubCategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->transform(
                function($sub_category)
                {
                    return [
                        'id' => $sub_category->id,
                        'name' => $sub_category->name,
                        'slug' => $sub_category->slug,
                        'category_name' => $sub_category->category->name,
                        'created_at' => $sub_category->created_at->toFormattedDateString(),
                    ];
                }
            )
        ];
    }
}
