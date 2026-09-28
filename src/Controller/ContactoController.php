<?php

namespace App\Controller;

use App\Entity\Contacto;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactoController extends AbstractController
{
    #[Route('/contacto/nuevo', name: 'nuevo_contacto', methods: ['GET', 'POST'])]
    public function nuevo(ManagerRegistry $doctrine, Request $request): Response
    {
        // Comprobar si el usuario está logueado
        if (!$this->getUser()) {
            return $this->redirectToRoute('inicio');
        }

        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $telefono = $request->request->get('telefono');
            $email = $request->request->get('email');

            if ($nombre && $telefono && $email) {
                $contacto = new Contacto();
                $contacto->setNombre($nombre);
                $contacto->setTelefono($telefono);
                $contacto->setEmail($email);

                $entityManager = $doctrine->getManager();
                $entityManager->persist($contacto);
                $entityManager->flush();

                return $this->redirectToRoute('contacto', ['codigo' => $contacto->getId()]);
            }
        }

        return $this->render('nuevo_contacto.html.twig');
    }

    #[Route('/contacto/{codigo}', name: 'contacto', methods: ['GET', 'POST'])]
    public function ficha(ManagerRegistry $doctrine, Request $request, int $codigo = 1): Response
    {
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        if (!$contacto) {
            return $this->redirectToRoute('inicio');
        }

        // Si el usuario envía el formulario para guardar o borrar
        if ($request->isMethod('POST')) {
            // Comprobar si el usuario está logueado
            if (!$this->getUser()) {
                return $this->redirectToRoute('inicio');
            }

            $entityManager = $doctrine->getManager();
            $accion = $request->request->get('accion');

            if ($accion === 'guardar') {
                $contacto->setNombre($request->request->get('nombre'));
                $contacto->setTelefono($request->request->get('telefono'));
                $contacto->setEmail($request->request->get('email'));
                $entityManager->flush();

                return $this->redirectToRoute('contacto', ['codigo' => $contacto->getId()]);
            } elseif ($accion === 'borrar') {
                $entityManager->remove($contacto);
                $entityManager->flush();

                return $this->redirectToRoute('inicio');
            }
        }

        return $this->render('ficha_contacto.html.twig', [
            'contacto' => $contacto,
        ]);
    }

    #[Route('/contacto/nuevo/{nombre}/{telefono}/{email}', name: 'nuevo-con-datos')]
    public function nuevoConDatos(
        ManagerRegistry $doctrine,
        string $nombre,
        string $telefono,
        string $email
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('inicio');
        }

        $contacto = new Contacto();
        $contacto->setNombre($nombre);
        $contacto->setTelefono($telefono);
        $contacto->setEmail($email);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($contacto);
        $entityManager->flush();

        return $this->redirectToRoute('contacto', ["codigo" => $contacto->getId()]);
    }

    #[Route('/contacto/update/{codigo?1}', name: 'update')]
    public function update(ManagerRegistry $doctrine, $codigo): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('inicio');
        }

        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        if ($contacto) {
            $contacto->setNombre("Nombre cambiado");
            $entityManager->persist($contacto);
            $entityManager->flush();
        }

        return $this->render("ficha_contacto.html.twig", ["contacto" => $contacto]);
    }
}