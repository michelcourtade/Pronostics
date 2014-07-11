<?php
namespace Dwf\PageBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Dwf\PageBundle\Entity\Page;

class PageAdmin extends Admin
{

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
        $collection->add('show',  $this->getRouterIdParameter().'/show');
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        ->add('title')
        ->add('content', 'textarea', array(
            'attr' => array(
                'class' => 'tinymce',
                'data-theme' => 'advanced'
            )
        ));
        
        $subject = $this->getSubject();
        if ($subject->getId()) {
            $optImg = array('label' => 'Heading image', 'required' => false);
        
            if ($subject->hasImage()) {
                $optImg['required'] = false;
                $container = $this->getConfigurationPool()->getContainer();
                $fullPath = $container->get('request')->getBasePath().$subject->getWebPath();
            
                $optImg['help'] = '<img src="'.$fullPath.'" width="200" />';
            }
            $formMapper->add('file', 'file', $optImg);
            
            if($subject->hasImage()) {
                $formMapper
                ->add('delete_file', 'checkbox', array('mapped' => false, 'required' => false))
                ->end();
            }
        }
        
        $formMapper->add('active', null, array('required' => false))

        ->with('Seo')
            ->add('linkRewrite')
            ->add('metaTitle')
            ->add('metaDescription')
            ;
    }
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ->add('title')
        ->add('created_at')
        ->add('updated_at')
        ->add('active')
        ;
    }
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
        ->addIdentifier('id')
        ->add('title')
        ->add('created_at')
        ->add('updated_at')
        ->add('active')
        ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                    'show' => array()
                )
        ))
        ;
    }
    
    public function prePersist($page) {
        $this->manageFileUpload($page);
    }
    
    public function preUpdate($page) {
        if ($page->getId()) {
            if ($page->hasImage()) {
                $fieldData = $this->getForm()->get('delete_file')->getData();
                if($fieldData === true) {
                    $page->setFile(null);
                    @unlink($page->getAbsolutePath());
                }
            }
        }
        $this->manageFileUpload($page);
    }
    
    private function manageFileUpload($page) {
        if ($page->getFile()) {
            $page->refreshUpdated();
        }
    }

}