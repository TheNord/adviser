<?php

namespace App\Http\Controllers\Admin;

use App\Entity\Banner\Banner;
use App\Entity\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Banner\EditRequest;
use App\Http\Requests\Banner\RejectRequest;
use App\UseCases\Banners\BannerService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    private $service;

    /** Загрузка сервиса и контроль доступа на управление баннерами */
    public function __construct(BannerService $service)
    {
        $this->service = $service;
        $this->middleware('can:manage-banners');
    }

    /** Отображение всех баннеров и система фильтрации */
    public function index(Request $request)
    {
        $query = Banner::orderByDesc('updated_at');

        if (!empty($value = $request->get('id'))) {
            $query->where('id', $value);
        }

        if (!empty($value = $request->get('user'))) {
            $query->where('user_id', $value);
        }

        if (!empty($value = $request->get('region'))) {
            $query->where('region_id', $value);
        }

        if (!empty($value = $request->get('category'))) {
            $query->where('category_id', $value);
        }

        if (!empty($value = $request->get('status'))) {
            $query->where('status', $value);
        }

        $banners = $query->paginate(20);

        $statuses = Banner::statusesList();

        return view('admin.banners.index', compact('banners', 'statuses'));
    }

    /** Просмотр баннера */
    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    /** Форма редактирования */
    public function editForm(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /** Редактирование баннера от администратора */
    public function edit(EditRequest $request, Banner $banner)
    {
        try {
            $this->service->editByAdmin($banner->id, $request);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.banners.show', $banner);
    }

    /** Модерация (принять) баннера */
    public function moderate(Banner $banner)
    {
        try {
            $this->service->moderate($banner->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.banners.show', $banner);
    }

    /** Форма отклонения баннера на модерации */
    public function rejectForm(Banner $banner)
    {
        return view('admin.banners.reject', compact('banner'));
    }

    /** Отклонение модерации баннера */
    public function reject(RejectRequest $request, Banner $banner)
    {
        try {
            $this->service->reject($banner->id, $request);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.banners.show', $banner);
    }

    /** Ручная пометка баннера оплаченным */
    public function pay(Banner $banner)
    {
        try {
            $this->service->pay($banner->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.banners.show', $banner);
    }

    /** Удаление любого баннера */
    public function destroy(Banner $banner)
    {
        try {
            $this->service->removeByAdmin($banner->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.banners.index');
    }
}
