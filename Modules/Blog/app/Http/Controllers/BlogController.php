<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Blog\Models\BlogPost;
use Modules\Cart\Models\Coupon;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $posts = BlogPost::query()
            ->with('author')
            ->when(! auth()->check(), fn ($query) => $query->published())
            ->latest('published_at')
            ->latest()
            ->get()
            ->map(fn (BlogPost $post) => $post->toBlogArray());

        return Inertia::render('Blog::Index', [
            'posts' => $posts,
        ]);
    }

    public function adminIndex(): Response
    {
        $posts = BlogPost::query()
            ->with('author')
            ->latest('published_at')
            ->latest()
            ->get()
            ->map(fn (BlogPost $post) => [
                'id' => $post->getKey(),
                'slug' => $post->slug,
                'title' => $post->title,
                'excerpt' => $post->excerpt ?: Str::limit(strip_tags($post->content), 120),
                'category' => $post->category,
                'image' => $post->image ?: '/modules/blog/images/ecommerce.png',
                'author' => $post->author_name ?: $post->author?->name ?: 'StoreMint Team',
                'created_by' => $post->created_by,
                'published' => $post->isPublished(),
                'published_at' => $post->published_at?->format('Y-m-d'),
                'published_at_formatted' => $post->published_at?->format('M d, Y'),
                'created_at' => $post->created_at->format('Y-m-d'),
                'created_at_formatted' => $post->created_at->format('M d, Y'),
                'updated_at' => $post->updated_at->format('M d, Y'),
            ]);

        return Inertia::render('Blog::AdminIndex', [
            'posts' => $posts,
            'categories' => $this->categories(),
            'authors' => $this->authors(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Blog::Create', [
            'post' => $this->emptyPost(),
            'categories' => $this->categories(),
            'authors' => $this->authors(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBlogPost($request);
        $returnToAdmin = $request->boolean('return_to_admin');
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['title']);
        $validated['read_time_minutes'] = $this->readTimeMinutes($validated['content']);
        $validated['created_by'] = $request->user()?->id;
        $validated['published_at'] = $request->boolean('is_published') ? now() : null;

        $post = BlogPost::create($validated);

        $redirect = $returnToAdmin
            ? redirect()->route('blog.adminIndex')
            : redirect()->route('blog.show', $post);

        return $redirect->with('toast', [
                'type' => 'success',
                'message' => '📝 Blog post created successfully!',
            ]);
    }

    /**
     * Show the specified resource.
     */
    public function show(BlogPost $blog): Response
    {
        if (! $blog->isPublished() && ! auth()->check()) {
            abort(404, 'Article not found');
        }

        $businessId = config('ecommerce.business_id', 1);
        $locationId = config('ecommerce.location_id', 1);

        // 1. Last 5 Blog Posts (excluding current post)
        $recentPosts = BlogPost::query()
            ->published()
            ->where('id', '!=', $blog->id)
            ->latest('published_at')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (BlogPost $post) => $post->toBlogArray());

        // 2. Last 5 Active Coupons & Discounts
        $activeCoupons = Coupon::query()
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Coupon $coupon) {
                $isPercentage = $coupon->discount_type === 'percentage';
                $discountDisplay = $isPercentage
                    ? (float) $coupon->discount_value . '% OFF'
                    : '$' . number_format((float) $coupon->discount_value, 2) . ' OFF';

                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'description' => $coupon->description ?: 'Exclusive store discount',
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => (float) $coupon->discount_value,
                    'discount_display' => $discountDisplay,
                    'min_order_amount' => $coupon->min_order_amount ? (float) $coupon->min_order_amount : null,
                    'expires_at' => $coupon->expires_at?->format('M d, Y'),
                ];
            });

        // 3. Best Selling Products (top 5 products)
        $bestSellingProducts = Product::with(['category', 'variations'])
            ->where('business_id', $businessId)
            ->where('is_allow_ecom', 1)
            ->take(5)
            ->get()
            ->map(function ($product) use ($locationId) {
                $variation = $product->variations->first();
                $stock = $product->currentStock($locationId);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $variation ? $variation->slug : null,
                    'price' => $variation ? (float) $variation->default_sell_price : 0.0,
                    'compare_at_price' => ($variation && $variation->compare_at_price) ? (float) $variation->compare_at_price : null,
                    'image' => $product->image ?: '/modules/shop/images/placeholder.png',
                    'category' => $product->category ? $product->category->name : 'General',
                    'stock_status' => $stock > 0 ? 'in_stock' : 'out_of_stock',
                    'is_best_seller' => $variation ? (bool) $variation->is_best_seller : false,
                ];
            });

        return Inertia::render('Blog::Show', [
            'post' => $blog->load('author')->toBlogArray(),
            'recentPosts' => $recentPosts,
            'activeCoupons' => $activeCoupons,
            'bestSellingProducts' => $bestSellingProducts,
        ]);
    }

    public function edit(BlogPost $blog): Response
    {
        return Inertia::render('Blog::Edit', [
            'post' => $blog->toFormArray(),
            'categories' => $this->categories(),
            'authors' => $this->authors(),
        ]);
    }

    public function update(Request $request, BlogPost $blog): RedirectResponse
    {
        $returnToAdmin = $request->boolean('return_to_admin');
        $validated = $this->validateBlogPost($request, $blog);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['title'], $blog);
        $validated['read_time_minutes'] = $this->readTimeMinutes($validated['content']);
        $validated['published_at'] = $request->boolean('is_published')
            ? ($blog->published_at ?: now())
            : null;

        $blog->update($validated);

        $redirect = $returnToAdmin
            ? redirect()->route('blog.adminIndex')
            : redirect()->route('blog.show', $blog);

        return $redirect->with('toast', [
                'type' => 'success',
                'message' => '✅ Blog post updated successfully!',
            ]);
    }

    public function destroy(Request $request, BlogPost $blog): RedirectResponse
    {
        $blog->delete();

        $returnToAdmin = $request->boolean('return_to_admin', true);
        $redirect = $returnToAdmin
            ? redirect()->route('blog.adminIndex')
            : redirect()->route('blog.index');

        return $redirect->with('toast', [
            'type' => 'success',
            'message' => '🗑️ Blog post deleted successfully!',
        ]);
    }

    private function validateBlogPost(Request $request, ?BlogPost $blog = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string', 'min:20'],
            'category' => ['required', 'string', 'max:120'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'image' => ['nullable', 'string', 'max:255'],
            'is_published' => ['boolean'],
        ]);
    }

    private function emptyPost(): array
    {
        $user = auth()->user();

        return [
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'content' => '',
            'category' => 'General',
            'author_name' => $user?->name ?? '',
            'image' => '/modules/blog/images/ecommerce.png',
            'is_published' => true,
        ];
    }

    private function categories(): array
    {
        return BlogPost::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values()
            ->prepend('General')
            ->unique()
            ->values()
            ->all();
    }

    private function authors(): array
    {
        $userNames = User::query()->pluck('name')->filter()->toArray();

        $blogAuthors = BlogPost::query()
            ->with('author')
            ->get()
            ->map(fn (BlogPost $post) => $post->author_name ?: $post->author?->name)
            ->filter()
            ->toArray();

        $all = array_merge(['StoreMint Team'], $userNames, $blogAuthors);

        return array_values(array_unique(array_filter($all)));
    }

    private function readTimeMinutes(string $content): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($content)) / 200));
    }

    private function uniqueSlug(string $value, ?BlogPost $ignorePost = null): string
    {
        $baseSlug = Str::slug($value) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (BlogPost::query()
            ->where('slug', $slug)
            ->when($ignorePost, fn ($query) => $query->whereKeyNot($ignorePost->getKey()))
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
