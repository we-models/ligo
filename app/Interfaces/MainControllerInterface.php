<?php

namespace App\Interfaces;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use \Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Response;

interface MainControllerInterface {

    /**
     * @param Request $request
     * @return View|Factory|Application
     */
    public function index(Request $request): View|Factory|Application ;

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request):Response|JsonResponse;

    /**
     * @param Request $request
     * @return Response|Application|ResponseFactory
     */
    public function store(Request $request): Response|Application|ResponseFactory;

    /**
     * @param String $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     */
    public function show(String $lang, int $id): Response|JsonResponse|Application|ResponseFactory;

    /**
     * @param String $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     */
    public function edit(String $lang, int $id): Response|JsonResponse|Application|ResponseFactory;

    /**
     * @param Request $request
     * @param String $lang
     * @param int $id
     * @return Response|Application|ResponseFactory
     */
    public function update(Request $request, String $lang, int $id): Response|Application|ResponseFactory ;

    /**
     * @param String $lang
     * @param int $id
     * @return Response|JsonResponse|Application|ResponseFactory
     */
    public function destroy(String $lang, int $id): Response|JsonResponse|Application|ResponseFactory ;

    /**
     * @param Request $request
     * @param String $lang
     * @return Response|JsonResponse
     */
    public function logs(Request $request, String $lang): Response|JsonResponse;
}
