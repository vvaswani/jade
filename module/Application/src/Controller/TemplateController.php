<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Http\Response\Stream;
use Zend\Http\Headers;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Service\ActivityService;
use Application\Entity\Activity;
use Application\Entity\Template;
use Application\Entity\Template\Permission as TemplatePermission;
use Application\Form\ConfirmationForm;

class TemplateController extends AbstractActionController
{

    private $em;

    private $ams;

    private $as;

    public function __construct(EntityManager $em, ActivityService $ams, AuthenticationService $as)
    {
        $this->em = $em;
        $this->ams = $ams;
        $this->as = $as;
    }

    public function indexAction()
    {
        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('template.entity'), $this->url()->fromRoute('templates'));
        }

        $templates = $this->em->getRepository(Template::class)->findBy(array(), array('creationTime' => 'DESC'));
        return new ViewModel(array('templates' => $templates));
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('templates');
        }

        $template = $this->em->getRepository(Template::class)->find($id);
        if (!$template) {
            return $this->redirect()->toRoute('templates');
        }

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('template.entity'), $this->url()->fromRoute('templates'));
        }

        return new ViewModel(array(
            'template' => $template,
        ));
    }

    public function saveAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $template = $this->em->getRepository(Template::class)->find($id);

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $template) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('template.entity'), $this->url()->fromRoute('templates'));
        }

        if (!$template) {
            $template = new Template();
            $template->setCreationTime(new \DateTime("now"));
            $permission = new TemplatePermission();
            $permission->setUser($this->as->getIdentity());
            $permission->setName(Template::PERMISSION_MANAGE);
            $permission->setTemplate($template);
            $template->setPermissions(array($permission));
        }

        $builder = new AnnotationBuilder();
        $hydrator = new DoctrineHydrator($this->em);
        $form = $builder->createForm($template);
        $form->setHydrator($hydrator);
        $form->bind($template);

        // for update operations
        // a new file upload is not mandatory
        if (!empty($template->getFilename())) {
            $filenameHash = $template->getFilenameHash();
            $form->getInputFilter()->get('file')->setRequired(false);
        }

        $request = $this->getRequest();
        if ($request->isPost()){
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($post);

            if ($form->isValid()) {
                $file = $template->getFile();
                $filename = $file['name'];

                // for update operations
                // if a new file is uploaded, delete the old file
                if (!empty($filename)) {
                    $fileObj = Template::UPLOAD_PATH . '/' . $filenameHash;
                    if (file_exists($fileObj)) {
                        unlink($fileObj);
                    }
                    $template->setFilename($filename);
                }
                $this->em->persist($template);
                $this->em->flush();
                // for update operations
                // if a new file is uploaded, save the new file to disk
                // and update the database with the new filename
                if (!empty($filename)) {
                    $filenameHash = (int)$template->getId() . '_' . md5($filename . microtime());
                    $filter = new \Zend\Filter\File\RenameUpload();
                    $filter->setTarget(Template::UPLOAD_PATH . '/' . $filenameHash);
                    $filter->filter($file);
                    $template->setFilenameHash($filenameHash);
                    $this->em->persist($template);
                    $this->em->flush();
                }
                $this->ams->flush();
                return $this->redirect()->toRoute('templates');
            }

        }

        return new ViewModel(array(
            'form' => $form,
            'id'  => $template->getId(),
            'filename' => $template->getFilename()
        ));
    }


    public function downloadAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('templates');
        }

        $template = $this->em->getRepository(Template::class)->find($id);
        if (!$template) {
            return $this->redirect()->toRoute('templates');
        }

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('template.entity'), $this->url()->fromRoute('templates'));
        }

        $file = Template::UPLOAD_PATH . '/' . $template->getFilenameHash();
        if (file_exists($file)) {
            $queue[] = array(
                Activity::OPERATION_REQUEST,
                new \DateTime("now"),
                $template,
                null,
                array('name' => $template->getName(), 'filename' => $template->getFilename())
            );
            $this->ams->setQueue($queue);
            $this->ams->flush();
            $response = new Stream();
            $response->setStream(fopen($file, 'r'));
            $response->setStatusCode(200);
            $response->setStreamName($template->getFilename());
            $headers = new Headers();
            $headers->addHeaders(array(
                'Content-Disposition' => 'attachment; filename="' . $template->getFilename() .'"',
                'Content-Type' => 'application/octet-stream',
                'Content-Length' => filesize($file),
                'Expires' => '@0',
                'Cache-Control' => 'must-revalidate',
                'Pragma' => 'public'
            ));
            $response->setHeaders($headers);
            return $response;
        }
        return $this->redirect()->toRoute('templates');
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('templates');
        }

        $template = $this->em->getRepository(Template::class)->find($id);
        if (!$template) {
            return $this->redirect()->toRoute('templates');
        }

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $template) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('template.entity'), $this->url()->fromRoute('templates'));
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('templates', array('action' => 'delete', 'id' => $id)));

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $fileObject = Template::UPLOAD_PATH . '/' . $template->getFilenameHash();
                    if (file_exists($fileObject)) {
                        unlink($fileObject);
                    }
                    $this->em->remove($template);
                    $this->em->flush();
                    $this->ams->flush();
                }
            }
            return $this->redirect()->toRoute('templates');
        }

        return $this->confirmationPlugin()->confirm(
            'common.confirm-delete',
            array (
                array('template.entity', 'lower', 'false'),
                array($template->getName(), 'none', 'true'),
            ),
            $form,
            $this->url()->fromRoute('templates')
        );

    }

}