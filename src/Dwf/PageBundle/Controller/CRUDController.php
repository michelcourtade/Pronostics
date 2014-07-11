<?php
// src/Dwf/PageBundle/Controller/CRUDController.php
namespace Dwf\PageBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;

class CRUDController extends Controller
{
    public function ShowAction($id = null)
    {
        $object = $this->admin->getObject($id);
    
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
    
        return $this->redirect($this->generateUrl('DwfPageBundle_page', array('id' => $object->getId(), 'linkRewrite' => $object->getLinkRewrite())));
    }
}