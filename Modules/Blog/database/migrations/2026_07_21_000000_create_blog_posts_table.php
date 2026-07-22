<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('blog_posts')) {
            Schema::create('blog_posts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('excerpt')->nullable();
                $table->longText('content');
                $table->string('category')->default('General');
                $table->string('author_name')->nullable();
                $table->string('image')->nullable();
                $table->unsignedSmallInteger('read_time_minutes')->default(1);
                $table->timestamp('published_at')->nullable()->index();
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (DB::table('blog_posts')->count() === 0) {
            $now = now();
            $posts = [
                [
                    'title' => 'Scaling StoreMint to 10k Orders: A Technical Deep Dive',
                    'excerpt' => 'How we optimized database connections, set up memory caches, and designed multi-tenant schema isolation to scale efficiently.',
                    'content' => "Scaling a modern e-commerce SaaS platform requires careful architectural planning. In this deep dive, we walk through how StoreMint handles scaling bottlenecks.\n\nFirst, we implemented granular Redis caching for product variation queries, reducing db response times by 80%. Second, our modular architecture isolates third-party package dependencies like Stripe and shipping handlers, allowing us to deploy updates with zero downtime.\n\nFinally, we optimized database seeders and queues to handle load spikes during flash sales. By separating read and write replica connections, we keep dashboard interactions snappy even under massive storefront traffic.",
                    'category' => 'Engineering',
                    'author_name' => 'Alexander Vance',
                    'image' => '/modules/blog/images/scaling.png',
                    'published_at' => $now->copy()->subDays(9),
                ],
                [
                    'title' => 'Mastering Multi-Tenancy Modular Systems in Laravel',
                    'excerpt' => 'Explore the design pattern of registering configurations, settings menus, and frontend Vue pages dynamically across multiple customer businesses.',
                    'content' => "A truly modular multi-tenant application allows customers to decide exactly what features they need. In StoreMint, this is solved by a dynamic system that merges module settings and routes on the fly.\n\nEach module (such as Gateways or Shipment) carries its own config schema, navigation rules, and Vue components. When an administrator enables a module, the system registers its routes, builds sidebar links, and mounts its settings pages dynamically.\n\nThis architecture keeps the core platform lightweight and allows engineers to deploy standalone modules across different Laravel + Vue codebases without modifying core files.",
                    'category' => 'Architecture',
                    'author_name' => 'Sarah Sterling',
                    'image' => '/modules/blog/images/architecture.png',
                    'published_at' => $now->copy()->subDays(11),
                ],
                [
                    'title' => '5 Conversion Optimization Secrets for Storefront Checkout',
                    'excerpt' => 'Reduce cart abandonment rates by implementing modern one-page checkouts, localized payment methods, and automated shipping estimations.',
                    'content' => "Cart abandonment remains the biggest challenge for online merchants. Research shows that a complex checkout flow is responsible for nearly 30% of abandoned sales.\n\nTo maximize conversions on your StoreMint storefront, focus on three key improvements:\n1. Enable Guest Checkout: Do not force users to register before they buy.\n2. Localize Gateway Providers: Offering cards via SSLCommerz alongside Stripe ensures customers have options they trust.\n3. Automatic Shipping Zones: Instantly calculate flat rates and estimated delivery times to avoid surprises at the final payment step.\n\nBy following these principles, you create a frictionless experience that keeps customers returning.",
                    'category' => 'E-Commerce',
                    'author_name' => 'Liam Sterling',
                    'image' => '/modules/blog/images/ecommerce.png',
                    'published_at' => $now->copy()->subDays(13),
                ],
            ];

            foreach ($posts as $post) {
                $contentWords = str_word_count(strip_tags($post['content']));

                DB::table('blog_posts')->insert([
                    ...$post,
                    'slug' => Str::slug($post['title']),
                    'read_time_minutes' => max(1, (int) ceil($contentWords / 200)),
                    'created_at' => $post['published_at'],
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
