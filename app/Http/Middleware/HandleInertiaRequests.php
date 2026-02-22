<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'navs' => $this->navs()
        ];
    }

    private function navs(): array
    {
        return [
            [
                'title' => 'Главная',
                'route' => 'dashboard',
            ],
            [
                'title' => 'Оценка уроков',
                'route' => 'lesson-grades.index',
            ],
            [
                'title' => 'Оценка сотрудников',
                'route' => 'employee-grades.index',
            ],
            [
                'title' => 'Мастер ИИ',
                'route' => 'mi.students.index',
            ],
            [
                'title' => 'Попытки',
                'route' => 'mi.students.stats',
            ],
        ];
    }
}
