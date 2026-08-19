<?php

namespace App\Livewire\Admin\Cms\Menus;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $menus = [];

    public int|string|null $selectedMenuId = null;

    /** @var array<string, mixed>|null */
    public ?array $selectedMenu = null;

    // Form: Create / Edit Menu Group
    public bool $showMenuForm = false;

    public int|string|null $editingMenuId = null;

    public string $menuName = '';

    public string $menuLocation = '';

    // Form: Add / Edit Item
    public bool $showItemForm = false;

    public int|string|null $editingItemId = null;   // null = add, filled = edit

    public string $itemType = 'custom';

    public string $itemTitle = '';

    public string $itemUrl = '';

    public string $itemTarget = '_self';

    public string|int $itemParentId = '';

    public string|int $selectedPageId = '';

    public function mount(): void
    {
        $this->loadMenus();
    }

    public function loadMenus(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $this->menus = Menu::where('school_id', $schoolId)->get()->toArray();
        if ($this->selectedMenuId) {
            $this->loadMenuItems();
        }
    }

    public function selectMenu(int|string $menuId): void
    {
        $this->selectedMenuId = $menuId;
        $this->loadMenuItems();
        $this->closeItemForm();
        $this->showMenuForm = false;
    }

    public function loadMenuItems(): void
    {
        $menu = $this->accessibleMenuQuery()->with(['parentItems.children'])->whereKey($this->selectedMenuId)->first();
        $this->selectedMenu = $menu ? $menu->toArray() : null;
    }

    // ──────────────────────────────────────────────────────────
    // MENU GROUP CRUD
    // ──────────────────────────────────────────────────────────

    public function openMenuForm(int|string|null $menuId = null): void
    {
        $this->editingMenuId = $menuId;
        $this->menuName = '';
        $this->menuLocation = '';
        if ($menuId) {
            $menu = $this->accessibleMenuQuery()->whereKey($menuId)->firstOrFail();
            $this->menuName = $menu->name;
            $this->menuLocation = $menu->location ?? '';
        }
        $this->showMenuForm = true;
    }

    public function saveMenu(): void
    {
        $this->validate([
            'menuName' => 'required|string|max:255',
            'menuLocation' => 'nullable|string|max:100',
        ]);

        $schoolId = app(CurrentSchool::class)->id();

        if ($this->editingMenuId) {
            $menu = $this->accessibleMenuQuery()->whereKey($this->editingMenuId)->firstOrFail();
            $menu->update([
                'name' => $this->menuName,
                'location' => $this->menuLocation ?: null,
            ]);
            session()->flash('message', 'Menu berhasil diperbarui.');
        } else {
            $menu = Menu::create([
                'school_id' => $schoolId,
                'name' => $this->menuName,
                'location' => $this->menuLocation ?: null,
            ]);
            session()->flash('message', 'Menu berhasil dibuat.');
        }

        $this->menuName = '';
        $this->menuLocation = '';
        $this->editingMenuId = null;
        $this->showMenuForm = false;
        $this->loadMenus();
        $this->selectMenu($menu->id);
    }

    public function deleteMenu(int|string $menuId): void
    {
        $this->accessibleMenuQuery()->whereKey($menuId)->firstOrFail()->delete();
        if ($this->selectedMenuId === $menuId) {
            $this->selectedMenuId = null;
            $this->selectedMenu = null;
        }
        $this->loadMenus();
        session()->flash('message', 'Menu berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────
    // MENU ITEM CRUD
    // ──────────────────────────────────────────────────────────

    public function openItemForm(int|string|null $itemId = null): void
    {
        $this->resetItemForm();
        $this->showItemForm = true;

        if ($itemId) {
            $this->editingItemId = $itemId;
            $item = $this->accessibleMenuItemQuery()->whereKey($itemId)->firstOrFail();
            $this->itemTitle = $item->title;
            $this->itemUrl = $item->url;
            $this->itemTarget = $item->target;
            $this->itemParentId = $item->parent_id ?? '';
        }
    }

    public function closeItemForm(): void
    {
        $this->showItemForm = false;
        $this->resetItemForm();
    }

    private function resetItemForm(): void
    {
        $this->editingItemId = null;
        $this->itemType = 'custom';
        $this->itemTitle = '';
        $this->itemUrl = '';
        $this->itemTarget = '_self';
        $this->itemParentId = '';
        $this->selectedPageId = '';
    }

    public function updatedItemType(): void
    {
        $this->itemTitle = '';
        $this->itemUrl = '';
        $this->selectedPageId = '';
    }

    public function updatedSelectedPageId(int|string|null $val): void
    {
        if ($val) {
            $page = Page::where('school_id', app(CurrentSchool::class)->id())->whereKey($val)->first();
            if ($page) {
                $this->itemTitle = $page->title;
                $this->itemUrl = url('/p/'.$page->slug);
            }
        }
    }

    public function saveItem(): void
    {
        $this->validate([
            'itemTitle' => 'required|string|max:255',
            'itemUrl' => 'nullable|string|max:500',
            'itemTarget' => 'required|in:_self,_blank',
        ]);

        if ($this->itemUrl !== '' && ! $this->isSafeMenuUrl($this->itemUrl)) {
            $this->addError('itemUrl', 'URL menu harus berupa tautan relatif, http, https, atau mailto.');

            return;
        }

        $menu = $this->accessibleMenuQuery()->whereKey($this->selectedMenuId)->firstOrFail();
        $parentId = $this->itemParentId ?: null;
        if ($parentId && ! MenuItem::query()->where('menu_id', $menu->id)->whereKey($parentId)->exists()) {
            abort(403);
        }

        if ($this->editingItemId) {
            // UPDATE existing item
            $item = $this->accessibleMenuItemQuery()->whereKey($this->editingItemId)->firstOrFail();
            $item->update([
                'title' => $this->itemTitle,
                'url' => $this->itemUrl ?: '#',
                'target' => $this->itemTarget,
                'parent_id' => $parentId,
            ]);
            session()->flash('message', 'Item menu berhasil diperbarui.');
        } else {
            // CREATE new item
            $count = MenuItem::where('menu_id', $menu->id)
                ->where('parent_id', $parentId)
                ->count();

            MenuItem::create([
                'menu_id' => $menu->id,
                'title' => $this->itemTitle,
                'url' => $this->itemUrl ?: '#',
                'target' => $this->itemTarget,
                'parent_id' => $parentId,
                'order' => $count,
            ]);
            session()->flash('message', 'Item menu berhasil ditambahkan.');
        }

        $this->closeItemForm();
        $this->loadMenuItems();
    }

    public function deleteItem(int|string $itemId): void
    {
        $this->accessibleMenuItemQuery()->whereKey($itemId)->firstOrFail()->delete();
        $this->loadMenuItems();
        session()->flash('message', 'Item menu berhasil dihapus.');
    }

    public function moveUp(int|string $itemId): void
    {
        $item = $this->accessibleMenuItemQuery()->whereKey($itemId)->first();
        if (! $item) {
            return;
        }
        $prev = MenuItem::where('menu_id', $item->menu_id)
            ->where('parent_id', $item->parent_id)
            ->where('order', '<', $item->order)
            ->orderBy('order', 'desc')->first();
        if ($prev) {
            [$item->order, $prev->order] = [$prev->order, $item->order];
            $item->save();
            $prev->save();
        }
        $this->loadMenuItems();
    }

    public function moveDown(int|string $itemId): void
    {
        $item = $this->accessibleMenuItemQuery()->whereKey($itemId)->first();
        if (! $item) {
            return;
        }
        $next = MenuItem::where('menu_id', $item->menu_id)
            ->where('parent_id', $item->parent_id)
            ->where('order', '>', $item->order)
            ->orderBy('order', 'asc')->first();
        if ($next) {
            [$item->order, $next->order] = [$next->order, $item->order];
            $item->save();
            $next->save();
        }
        $this->loadMenuItems();
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $pages = Page::where('school_id', $schoolId)->where('status', 'Published')->get();

        return view('livewire.admin.cms.menus.index', [
            'pages' => $pages,
        ]);
    }

    /** @return Builder<Menu> */
    private function accessibleMenuQuery(): Builder
    {
        return Menu::query()->where('school_id', app(CurrentSchool::class)->id());
    }

    /** @return Builder<MenuItem> */
    private function accessibleMenuItemQuery(): Builder
    {
        return MenuItem::query()->whereHas('menu', function ($query): void {
            $query->where('school_id', app(CurrentSchool::class)->id());
        });
    }

    private function isSafeMenuUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https', 'mailto'], true);
    }
}
