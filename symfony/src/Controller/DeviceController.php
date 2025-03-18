<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeviceController extends AbstractController
{
    #[Route('/devices', name: 'app_device')]
    public function index(): Response
    {
        return $this->render('device/index.html.twig', [
            'controller_name' => 'DeviceController',
        ]);
    }

    #[Route('/devices/edit/{id}', name: 'app_device_edit')]
    public function edit(int $id = null): Response
    {
        return $this->render('device/edit.html.twig', [
            'device_id' => $id,
            'edit_mode' => $id ? true : false,
        ]);
    }

    #[Route('/devices/issue', name: 'app_device_issue')]
    public function issue(): Response
    {
        return $this->render('device/issue.html.twig', [
            'controller_name' => 'DeviceController',
        ]);
    }

    #[Route('/devices/return', name: 'app_device_return')]
    public function return(): Response
    {
        return $this->render('device/return.html.twig', [
            'controller_name' => 'DeviceController',
        ]);
    }
}
