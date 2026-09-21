<?php

namespace App\Controller;

use App\Entity\Contacto;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactoController extends AbstractController
{
    #[Route('/contacto/{codigo}', name: 'contacto')]
    public function ficha(ManagerRegistry $doctrine, int $codigo = 1): Response
    {
    // La primera instrucción suele ser esta, ya que cogemos el repositorio de la entidad asociada
    $repositorio = $doctrine->getRepository(Contacto::class);
    // Ahora usamos uno de los métodos del repositorio
    $contacto = $repositorio->find($codigo);
    
    return $this ->render('ficha_contacto.html.twig', [
        'contacto'=>$contacto,
    ]);
    }   

    #[Route('/contacto/nuevo/{nombre}/{telefono}/{email}', name: 'nuevo-con-datos')]
    public function nuevoConDatos(
    ManagerRegistry $doctrine,
    string $nombre,
    string $telefono,
    string $email
) {
    // Crear un nuevo contacto
    $contacto = new Contacto();

    // Asignar los datos del contacto
    $contacto->setNombre($nombre);
    $contacto->setTelefono($telefono);
    $contacto->setEmail($email);

    // Guardar el contacto
    $entityManager = $doctrine->getManager();
    $entityManager->persist($contacto);
    $entityManager->flush();

    // Redirigir a la ficha del contacto
    return $this->redirectToRoute('contacto', ["codigo" => $contacto->getId()]);
}
    #[Route('/contacto/update/{codigo?1}', name: 'update')]
    public function update(ManagerRegistry $doctrine, $codigo): Response
    {
    $entityManager = $doctrine->getManager();
    
    // Se coge el repositorio de la entidad Contacto o de la que se quiera
    $repositorio = $doctrine->getRepository(Contacto::class);
    
    // Se busca el contacto que tenga el id = $codigo
    // El método `find` siempre busca por la clave de la tabla, que suele ser `id`
    $contacto = $repositorio->find($codigo);
    
    // Cambiamos un dato, por ejemplo el nombre
    $contacto->setNombre("Nombre cambiado");
    
    // Guardamos de forma temporal
    $entityManager->persist($contacto);
    
    try{
        // y no nos olvidemos de guardar en la base de datos
        $entityManager->flush();
        
        // Mostramos la plantilla pasándole el contacto como parámetro
        return $this->render("ficha_contacto.html.twig", ["contacto" => $contacto]);
    }catch (\Exception $e){
        return new Response("Se ha producido un error: " . $e->getMessage());
    }
    }
}