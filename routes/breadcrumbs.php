<?php

use App\Entity\Adverts\Advert\Advert;
use App\Entity\Adverts\Advert\Dialog\Dialog;
use App\Entity\Adverts\Attribute;
use App\Entity\Adverts\Category;
use App\Entity\Banner\Banner;
use App\Entity\Page;
use App\Entity\Region;
use App\Entity\Ticket\Ticket;
use App\Entity\User;
use App\Http\Router\AdvertsPath;
use App\Http\Router\PagePath;
use DaveJamesMiller\Breadcrumbs\BreadcrumbsGenerator as Crumbs;

Breadcrumbs::register('home', function (Crumbs $crumbs) {
    $crumbs->push('Home', route('home'));
});

Breadcrumbs::register('login', function (Crumbs $crumbs) {
    $crumbs->parent('home');
    $crumbs->push('Login', route('login'));
});

Breadcrumbs::register('login.phone', function (Crumbs $crumbs) {
    $crumbs->parent('home');
    $crumbs->push('Login', route('login.phone'));
});

Breadcrumbs::register('register', function (Crumbs $crumbs) {
    $crumbs->parent('home');
    $crumbs->push('Register', route('register'));
});

Breadcrumbs::register('password.request', function (Crumbs $crumbs) {
    $crumbs->parent('login');
    $crumbs->push('Reset Password', route('password.request'));
});

Breadcrumbs::register('password.reset', function (Crumbs $crumbs) {
    $crumbs->parent('password.request');
    $crumbs->push('Change', route('password.reset'));
});

// Pages привязывается к web.php -> name('page')

Breadcrumbs::register('page', function (Crumbs $crumbs, PagePath $path) {
    // если у текущей страницы имеется родитель
    if ($parent = $path->page->parent) {
        // добавляем его выше
        $crumbs->parent('page', $path->withPage($path->page->parent));
    } else {
        // как закончились выводим главную
        $crumbs->parent('home');
    }
    // передаем имя страницы и путь
    $crumbs->push($path->page->title, route('page', $path));
});


// Adverts

Breadcrumbs::register('adverts.inner_region', function (Crumbs $crumbs, AdvertsPath $path) {
    if ($path->region && $parent = $path->region->parent) {
        $crumbs->parent('adverts.inner_region', $path->withRegion($parent));
    } else {
        $crumbs->parent('home');
        $crumbs->push('Adverts', route('adverts.index'));
    }
    if ($path->region) {
        $crumbs->push($path->region->name, route('adverts.index', $path));
    }
});

Breadcrumbs::register('adverts.inner_category', function (Crumbs $crumbs, AdvertsPath $path, AdvertsPath $orig) {
    if ($path->category && $parent = $path->category->parent) {
        $crumbs->parent('adverts.inner_category', $path->withCategory($parent), $orig);
    } else {
        $crumbs->parent('adverts.inner_region', $orig);
    }
    if ($path->category) {
        $crumbs->push($path->category->name, route('adverts.index', $path));
    }
});

Breadcrumbs::register('adverts.index', function (Crumbs $crumbs, AdvertsPath $path = null) {
    $path = $path ?: adverts_path(null, null);
    $crumbs->parent('adverts.inner_category', $path, $path);
});

Breadcrumbs::register('adverts.show', function (Crumbs $crumbs, Advert $advert) {
    $crumbs->parent('adverts.index', adverts_path($advert->region, $advert->category));
    $crumbs->push($advert->title, route('adverts.show', $advert));
});

Breadcrumbs::register('adverts.cabinet.show', function (Crumbs $crumbs, Advert $advert) {
    $crumbs->parent('adverts.index');
    $crumbs->push($advert->title, route('adverts.show', $advert));
});

// Объявление >> Диалог
Breadcrumbs::register('adverts.message.dialog', function (Crumbs $crumbs, Advert $advert) {
    $crumbs->parent('adverts.show', $advert);
    $crumbs->push('Диалоги', route('adverts.message.dialog', $advert));
});

// Cabinet

Breadcrumbs::register('cabinet.home', function (Crumbs $crumbs) {
    $crumbs->parent('home');
    $crumbs->push('Cabinet', route('cabinet.home'));
});

Breadcrumbs::register('cabinet.profile.home', function (Crumbs $crumbs) {
    $crumbs->parent('cabinet.home');
    $crumbs->push('Profile', route('cabinet.profile.home'));
});

Breadcrumbs::register('cabinet.profile.edit', function (Crumbs $crumbs) {
    $crumbs->parent('cabinet.profile.home');
    $crumbs->push('Edit', route('cabinet.profile.edit'));
});

Breadcrumbs::register('cabinet.profile.phone', function (Crumbs $crumbs) {
    $crumbs->parent('cabinet.profile.home');
    $crumbs->push('Phone', route('cabinet.profile.phone'));
});

// Cabinet >> Messages

Breadcrumbs::register('cabinet.messages.index', function (Crumbs $crumbs) {
    $crumbs->parent('cabinet.home');
    $crumbs->push('Messages', route('cabinet.messages.index'));
});

Breadcrumbs::register('cabinet.messages.show', function (Crumbs $crumbs, Dialog $dialog) {
    $crumbs->parent('cabinet.messages.index');
    $crumbs->push($dialog->advert->title, route('cabinet.messages.show', $dialog));
});

// Кабинет >> Объявление >> Диалог
Breadcrumbs::register('cabinet.adverts.message', function (Crumbs $crumbs, Advert $advert) {
    $crumbs->parent('adverts.cabinet.show', $advert);
    $crumbs->push('Dialogs', route('cabinet.adverts.message', $advert));
});

// Cabinet >> Adverts

Breadcrumbs::register('cabinet.adverts.index', function (Crumbs $crumbs) {
    $crumbs->parent('cabinet.home');
    $crumbs->push('Adverts', route('cabinet.adverts.index'));
});

Breadcrumbs::register('cabinet.adverts.create', function (Crumbs $crumbs) {
    $crumbs->parent('adverts.index');
    $crumbs->push('Create', route('cabinet.adverts.create'));
});

Breadcrumbs::register('cabinet.adverts.photos', function (Crumbs $crumbs, Advert $advert) {
    $crumbs->parent('adverts.cabinet.show', $advert);
    $crumbs->push('Photos', route('cabinet.adverts.photos', $advert));
});

Breadcrumbs::register('cabinet.adverts.edit', function (Crumbs $crumbs, Advert $advert) {
    $crumbs->parent('adverts.cabinet.show', $advert);
    $crumbs->push('Photos', route('cabinet.adverts.edit', $advert));
});

Breadcrumbs::register('cabinet.adverts.create.region', function (Crumbs $crumbs, Category $category, Region $region = null) {
    $crumbs->parent('cabinet.adverts.create');
    $crumbs->push($category->name, route('cabinet.adverts.create.region', [$category, $region]));
});

Breadcrumbs::register('cabinet.adverts.create.advert', function (Crumbs $crumbs, Category $category, Region $region = null) {
    $crumbs->parent('cabinet.adverts.create.region', $category, $region);
    $crumbs->push($region ? $region->name : 'All', route('cabinet.adverts.create.advert', [$category, $region]));
});

// Cabinet >> Favorites

Breadcrumbs::register('cabinet.favorites.index', function (Crumbs $crumbs) {
    $crumbs->parent('cabinet.home');
    $crumbs->push('Adverts', route('cabinet.favorites.index'));
});

// Cabinet >> Banners

Breadcrumbs::register('cabinet.banners.index', function (Crumbs $crumbs) {
    $crumbs->parent('cabinet.home');
    $crumbs->push('Banners', route('cabinet.banners.index'));
});

Breadcrumbs::register('cabinet.banners.show', function (Crumbs $crumbs, Banner $banner) {
    $crumbs->parent('cabinet.banners.index');
    $crumbs->push($banner->name, route('cabinet.banners.show', $banner));
});

Breadcrumbs::register('cabinet.banners.edit', function (Crumbs $crumbs, Banner $banner) {
    $crumbs->parent('cabinet.banners.show', $banner);
    $crumbs->push('Edit', route('cabinet.banners.edit', $banner));
});

Breadcrumbs::register('cabinet.banners.file', function (Crumbs $crumbs, Banner $banner) {
    $crumbs->parent('cabinet.banners.show', $banner);
    $crumbs->push('File', route('cabinet.banners.file', $banner));
});

Breadcrumbs::register('cabinet.banners.create', function (Crumbs $crumbs) {
    $crumbs->parent('cabinet.banners.index');
    $crumbs->push('Create', route('cabinet.banners.create'));
});

Breadcrumbs::register('cabinet.banners.create.region', function (Crumbs $crumbs, Category $category, Region $region = null) {
    $crumbs->parent('cabinet.banners.create');
    $crumbs->push($category->name, route('cabinet.banners.create.region', [$category, $region]));
});

Breadcrumbs::register('cabinet.banners.create.banner', function (Crumbs $crumbs, Category $category, Region $region = null) {
    $crumbs->parent('cabinet.banners.create.region', $category, $region);
    $crumbs->push($region ? $region->name : 'All', route('cabinet.banners.create.banner', [$category, $region]));
});

// Cabinet >> Tickets
Breadcrumbs::register('cabinet.tickets.index', function (Crumbs $crumbs) {
    $crumbs->parent('cabinet.home');
    $crumbs->push('Tickets', route('cabinet.tickets.index'));
});
Breadcrumbs::register('cabinet.tickets.create', function (Crumbs $crumbs) {
    $crumbs->parent('cabinet.tickets.index');
    $crumbs->push('Create', route('cabinet.tickets.create'));
});
Breadcrumbs::register('cabinet.tickets.show', function (Crumbs $crumbs, Ticket $ticket) {
    $crumbs->parent('cabinet.tickets.index');
    $crumbs->push($ticket->subject, route('cabinet.tickets.show', $ticket));
});


// Admin

Breadcrumbs::register('admin.home', function (Crumbs $crumbs) {
    $crumbs->parent('home');
    $crumbs->push('Admin', route('admin.home'));
});

// Admin >> Tickets
Breadcrumbs::register('admin.tickets.index', function (Crumbs $crumbs) {
    $crumbs->parent('admin.home');
    $crumbs->push('Tickets', route('admin.tickets.index'));
});
Breadcrumbs::register('admin.tickets.show', function (Crumbs $crumbs, Ticket $ticket) {
    $crumbs->parent('admin.tickets.index');
    $crumbs->push($ticket->subject, route('admin.tickets.show', $ticket));
});
Breadcrumbs::register('admin.tickets.edit', function (Crumbs $crumbs, Ticket $ticket) {
    $crumbs->parent('admin.tickets.show', $ticket);
    $crumbs->push('Edit', route('admin.tickets.edit', $ticket));
});

// Admin >> Users

Breadcrumbs::register('admin.users.index', function (Crumbs $crumbs) {
    $crumbs->parent('admin.home');
    $crumbs->push('Users', route('admin.users.index'));
});

Breadcrumbs::register('admin.users.create', function (Crumbs $crumbs) {
    $crumbs->parent('admin.users.index');
    $crumbs->push('Create', route('admin.users.create'));
});

Breadcrumbs::register('admin.users.show', function (Crumbs $crumbs, User $user) {
    $crumbs->parent('admin.users.index');
    $crumbs->push($user->name, route('admin.users.show', $user));
});

Breadcrumbs::register('admin.users.edit', function (Crumbs $crumbs, User $user) {
    $crumbs->parent('admin.users.show', $user);
    $crumbs->push('Edit', route('admin.users.edit', $user));
});

// Admin >> Banners

Breadcrumbs::register('admin.banners.index', function (Crumbs $crumbs) {
    $crumbs->parent('admin.home');
    $crumbs->push('Banners', route('admin.banners.index'));
});

Breadcrumbs::register('admin.banners.show', function (Crumbs $crumbs, Banner $banner) {
    $crumbs->parent('admin.banners.index');
    $crumbs->push($banner->name, route('admin.banners.show', $banner));
});

Breadcrumbs::register('admin.banners.edit', function (Crumbs $crumbs, Banner $banner) {
    $crumbs->parent('admin.banners.show', $banner);
    $crumbs->push('Edit', route('admin.banners.edit', $banner));
});

Breadcrumbs::register('admin.banners.reject', function (Crumbs $crumbs, Banner $banner) {
    $crumbs->parent('admin.banners.show', $banner);
    $crumbs->push('Reject', route('admin.banners.reject', $banner));
});

// Admin >> Regions

Breadcrumbs::register('admin.regions.index', function (Crumbs $crumbs) {
    $crumbs->parent('admin.home');
    $crumbs->push('Regions', route('admin.regions.index'));
});

Breadcrumbs::register('admin.regions.create', function (Crumbs $crumbs) {
    $crumbs->parent('admin.regions.index');
    $crumbs->push('Create', route('admin.regions.create'));
});

Breadcrumbs::register('admin.regions.show', function (Crumbs $crumbs, Region $region) {
    if ($parent = $region->parent) {
        $crumbs->parent('admin.regions.show', $parent);
    } else {
        $crumbs->parent('admin.regions.index');
    }
    $crumbs->push($region->name, route('admin.regions.show', $region));
});

Breadcrumbs::register('admin.regions.edit', function (Crumbs $crumbs, Region $region) {
    $crumbs->parent('admin.regions.show', $region);
    $crumbs->push('Edit', route('admin.regions.edit', $region));
});

// Admin >> Adverts

Breadcrumbs::register('admin.adverts.adverts.index', function (Crumbs $crumbs) {
    $crumbs->parent('admin.home');
    $crumbs->push('Categories', route('admin.adverts.adverts.index'));
});

Breadcrumbs::register('admin.adverts.adverts.edit', function (Crumbs $crumbs, Advert $advert) {
    $crumbs->parent('admin.home');
    $crumbs->push($advert->title, route('admin.adverts.adverts.edit', $advert));
});

Breadcrumbs::register('admin.adverts.adverts.reject', function (Crumbs $crumbs, Advert $advert) {
    $crumbs->parent('admin.home');
    $crumbs->push($advert->title, route('admin.adverts.adverts.reject', $advert));
});

Breadcrumbs::register('admin.adverts.adverts.photos', function (Crumbs $crumbs, Advert $advert) {
    $crumbs->parent('admin.home');
    $crumbs->push($advert->title, route('admin.adverts.adverts.photos', $advert));
});



// Admin >> Advert Categories

Breadcrumbs::register('admin.adverts.categories.index', function (Crumbs $crumbs) {
    $crumbs->parent('admin.home');
    $crumbs->push('Categories', route('admin.adverts.categories.index'));
});

Breadcrumbs::register('admin.adverts.categories.create', function (Crumbs $crumbs) {
    $crumbs->parent('admin.adverts.categories.index');
    $crumbs->push('Create', route('admin.adverts.categories.create'));
});

Breadcrumbs::register('admin.adverts.categories.show', function (Crumbs $crumbs, Category $category) {
    if ($parent = $category->parent) {
        $crumbs->parent('admin.adverts.categories.show', $parent);
    } else {
        $crumbs->parent('admin.adverts.categories.index');
    }
    $crumbs->push($category->name, route('admin.adverts.categories.show', $category));
});

Breadcrumbs::register('admin.adverts.categories.edit', function (Crumbs $crumbs, Category $category) {
    $crumbs->parent('admin.adverts.categories.show', $category);
    $crumbs->push('Edit', route('admin.adverts.categories.edit', $category));
});

// Admin >> Advert Category Attributes

Breadcrumbs::register('admin.adverts.categories.attributes.create', function (Crumbs $crumbs, Category $category) {
    $crumbs->parent('admin.adverts.categories.show', $category);
    $crumbs->push('Create', route('admin.adverts.categories.attributes.create', $category));
});

Breadcrumbs::register('admin.adverts.categories.attributes.show', function (Crumbs $crumbs, Category $category, Attribute $attribute) {
    $crumbs->parent('admin.adverts.categories.show', $category);
    $crumbs->push($attribute->name, route('admin.adverts.categories.attributes.show', [$category, $attribute]));
});

Breadcrumbs::register('admin.adverts.categories.attributes.edit', function (Crumbs $crumbs, Category $category, Attribute $attribute) {
    $crumbs->parent('admin.adverts.categories.attributes.show', $category, $attribute);
    $crumbs->push('Edit', route('admin.adverts.categories.attributes.edit', [$category, $attribute]));
});

// Admin >> Pages

Breadcrumbs::register('admin.pages.index', function (Crumbs $crumbs) {
    $crumbs->parent('admin.home');
    $crumbs->push('Pages', route('admin.pages.index'));
});

Breadcrumbs::register('admin.pages.create', function (Crumbs $crumbs) {
    $crumbs->parent('admin.pages.index');
    $crumbs->push('Create', route('admin.pages.create'));
});

Breadcrumbs::register('admin.pages.show', function (Crumbs $crumbs, Page $page) {
    // если у текущей страницы есть родитель
    if ($parent = $page->parent) {
        // передаем парент выше чтобы отобразить еще одну хлебную крошку
        $crumbs->parent('admin.pages.show', $parent);
    } else {
        // если родители закончились то выводим главную
        $crumbs->parent('admin.pages.index');
    }
    // добавляя название нашей страницы и ссылку на саму страницу
    $crumbs->push($page->title, route('admin.pages.show', $page));
});

Breadcrumbs::register('admin.pages.edit', function (Crumbs $crumbs, Page $page) {
    $crumbs->parent('admin.pages.show', $page);
    $crumbs->push('Edit', route('admin.pages.edit', $page));
});