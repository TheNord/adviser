<?php

namespace App\Http\Controllers\Admin;

use App\Entity\Page;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Pages\PageRequest;

class PageController extends Controller
{
    /** Контроль доступа к редактированию страниц */
    public function __construct()
    {
        $this->middleware('can:manage-pages');
    }

    /** Список всех страниц c глубиной */
    public function index()
    {
        // передаем все паренты для выпадающего списка
        $pages = Page::defaultOrder()->withDepth()->get();

        return view('admin.pages.index', compact('pages'));
    }

    /** Форма создания новой страницы */
    public function create()
    {
        $parents = Page::defaultOrder()->withDepth()->get();

        return view('admin.pages.create', compact('parents'));
    }

    /** Создание новой страницы */
    public function store(PageRequest $request)
    {
        $page = Page::create([
            'title' => $request['title'],
            'slug' => $request['slug'],
            'menu_title' => $request['menu_title'],
            'parent_id' => $request['parent'],
            'content' => $request['content'],
            'description' => $request['description'],
        ]);

        return redirect()->route('admin.pages.show', $page);
    }

    /** Форма отображение информации о странице */
    public function show(Page $page)
    {
        return view('admin.pages.show', compact('page'));
    }

    /** Форма редактирования страницы */
    public function edit(Page $page)
    {
        $parents = Page::defaultOrder()->withDepth()->get();
        return view('admin.pages.edit', compact('page', 'parents'));
    }

    /** Обновление страницы */
    public function update(PageRequest $request, Page $page)
    {
        $page->update([
            'title' => $request['title'],
            'slug' => $request['slug'],
            'menu_title' => $request['menu_title'],
            'parent_id' => $request['parent'],
            'content' => $request['content'],
            'description' => $request['description'],
        ]);
        return redirect()->route('admin.pages.show', $page);
    }

    /** Функции перемещения страницы по дереву */
    public function first(Page $page)
    {
        if ($first = $page->siblings()->defaultOrder()->first()) {
            $page->insertBeforeNode($first);
        }
        return redirect()->route('admin.pages.index');
    }

    public function up(Page $page)
    {
        $page->up();
        return redirect()->route('admin.pages.index');
    }

    public function down(Page $page)
    {
        $page->down();
        return redirect()->route('admin.pages.index');
    }

    public function last(Page $page)
    {
        if ($last = $page->siblings()->defaultOrder('desc')->first()) {
            $page->insertAfterNode($last);
        }
        return redirect()->route('admin.pages.index');
    }

    /** Удаление страницы */
    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index');
    }
}