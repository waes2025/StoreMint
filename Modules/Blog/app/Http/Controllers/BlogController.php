<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Get the mock blog posts.
     */
    private function getMockPosts(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Scaling StoreMint to 10k Orders: A Technical Deep Dive',
                'excerpt' => 'How we optimized database connections, set up memory caches, and designed multi-tenant schema isolation to scale efficiently.',
                'content' => "Scaling a modern e-commerce SaaS platform requires careful architectural planning. In this deep dive, we walk through how StoreMint handles scaling bottlenecks.\n\nFirst, we implemented granular Redis caching for product variation queries, reducing db response times by 80%. Second, our modular architecture isolates third-party package dependencies like Stripe and shipping handlers, allowing us to deploy updates with zero downtime.\n\nFinally, we optimized database seeders and queues to handle load spikes during flash sales. By separating read and write replica connections, we keep dashboard interactions snappy even under massive storefront traffic.",
                'category' => 'Engineering',
                'author' => 'Alexander Vance',
                'date' => 'Jul 12, 2026',
                'read_time' => '5 min',
                'image' => '/modules/blog/images/scaling.png'
            ],
            [
                'id' => 2,
                'title' => 'Mastering Multi-Tenancy Modular Systems in Laravel',
                'excerpt' => 'Explore the design pattern of registering configurations, settings menus, and frontend Vue pages dynamically across multiple customer businesses.',
                'content' => "A truly modular multi-tenant application allows customers to decide exactly what features they need. In StoreMint, this is solved by a dynamic system that merges module settings and routes on the fly.\n\nEach module (such as Gateways or Shipment) carries its own config schema, navigation rules, and Vue components. When an administrator enables a module, the system registers its routes, builds sidebar links, and mounts its settings pages dynamically.\n\nThis architecture keeps the core platform lightweight and allows engineers to deploy standalone modules across different Laravel + Vue codebases without modifying core files.",
                'category' => 'Architecture',
                'author' => 'Sarah Sterling',
                'date' => 'Jul 10, 2026',
                'read_time' => '4 min',
                'image' => '/modules/blog/images/architecture.png'
            ],
            [
                'id' => 3,
                'title' => '5 Conversion Optimization Secrets for Storefront Checkout',
                'excerpt' => 'Reduce cart abandonment rates by implementing modern one-page checkouts, localized payment methods, and automated shipping estimations.',
                'content' => "Cart abandonment remains the biggest challenge for online merchants. Research shows that a complex checkout flow is responsible for nearly 30% of abandoned sales.\n\nTo maximize conversions on your StoreMint storefront, focus on three key improvements:\n1. Enable Guest Checkout: Do not force users to register before they buy.\n2. Localize Gateway Providers: Offering cards via SSLCommerz alongside Stripe ensures customers have options they trust.\n3. Automatic Shipping Zones: Instantly calculate flat rates and estimated delivery times to avoid surprises at the final payment step.\n\nBy following these principles, you create a frictionless experience that keeps customers returning.",
                'category' => 'E-Commerce',
                'author' => 'Liam Sterling',
                'date' => 'Jul 08, 2026',
                'read_time' => '3 min',
                'image' => '/modules/blog/images/ecommerce.png'
            ]
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Blog::Index', [
            'posts' => $this->getMockPosts()
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id): Response
    {
        $posts = $this->getMockPosts();
        $post = collect($posts)->firstWhere('id', (int) $id);

        if (! $post) {
            abort(404, 'Article not found');
        }

        return Inertia::render('Blog::Show', [
            'post' => $post
        ]);
    }
}
