<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->default('general');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('home_contents', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('cta_text')->nullable();
            $table->text('intro_title')->nullable();
            $table->longText('intro_description')->nullable();
            $table->text('closing_title')->nullable();
            $table->longText('closing_description')->nullable();
            $table->timestamps();
        });

        Schema::create('about_contents', function (Blueprint $table) {
            $table->id();
            $table->string('major')->nullable();
            $table->text('description')->nullable();
            $table->longText('story')->nullable();
            $table->string('main_image')->nullable();
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->string('class_name')->nullable()->index();
            $table->string('major')->nullable()->index();
            $table->string('photo_path')->nullable();
            $table->text('quote')->nullable();
            $table->longText('bio')->nullable();
            $table->string('instagram')->nullable();
            $table->boolean('status')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->string('role')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('quote')->nullable();
            $table->longText('message')->nullable();
            $table->boolean('status')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->string('image_path');
            $table->string('category')->nullable()->index();
            $table->date('date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('status')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->text('content');
            $table->string('attribution')->nullable();
            $table->boolean('status')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_contents');
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('members');
        Schema::dropIfExists('about_contents');
        Schema::dropIfExists('home_contents');
        Schema::dropIfExists('site_settings');
    }
};