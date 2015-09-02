<?php

namespace KG\BeekeepingManagementBundle\Controller;

use KG\BeekeepingManagementBundle\Entity\Visite;
use KG\BeekeepingManagementBundle\Entity\Colonnie;
use KG\BeekeepingManagementBundle\Form\Type\VisiteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class VisiteController extends Controller
{
    /**
    * @Security("has_role('ROLE_USER')")
    * @ParamConverter("visite", options={"mapping": {"visite_id" : "id"}}) 
    */    
    public function viewAction(Visite $visite)
    {

    }

    /**
    * @Security("has_role('ROLE_USER')")
    */    
    public function deleteAction(Visite $visite)
    {

    }
    
    /**
    * @Security("has_role('ROLE_USER')")
    * @ParamConverter("colonnie", options={"mapping": {"colonnie_id" : "id"}})  
    */    
    public function addAction(Colonnie $colonnie, Request $request)
    {
        $exploitation = $colonnie->getExploitation();
        $apiculteurExploitations = $exploitation->getApiculteurExploitations();
        $not_permitted = true;
        
        foreach ( $apiculteurExploitations as $apiculteurExploitation ){
            if( $apiculteurExploitation->getApiculteur()->getId() == $this->getUser()->getId() ){
                $not_permitted = false;
                break;
            }
        }
        
        if( $not_permitted || $colonnie->getSupprime() ){
            throw new NotFoundHttpException('Page inexistante.');
        }       
        
        $visite = new Visite();
        $form = $this->createForm(new VisiteType, $visite);
        
        if ($form->handleRequest($request)->isValid()){
                        
            $visite->setColonnie($colonnie);
            $em = $this->getDoctrine()->getManager();
            $em->persist($visite);
            $em->flush();
        
            $request->getSession()->getFlashBag()->add('success','Visite créée avec succès');
        
            return $this->redirect($this->generateUrl('kg_beekeeping_management_view_visite', array('visite_id' => $visite->getId())));
        }

        return $this->render('KGBeekeepingManagementBundle:Visite:add.html.twig', 
                             array(
                                    'form'     => $form->createView(),
                                    'colonnie' => $colonnie
                ));        
    } 
}