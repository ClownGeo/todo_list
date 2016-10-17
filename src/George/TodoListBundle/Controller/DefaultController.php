<?php

namespace George\TodoListBundle\Controller;

use George\TodoListBundle\Entity\Notes;
use George\TodoListBundle\Helpers\MainHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends Controller
{
    /**
     * Контроллер обработки запроса главной страницы
     * @Route("/", name = "index_page")
     */
    public function indexAction(Request $request)
    {
        return $this->render('TodoListBundle:Default:index.html.twig', []);
    }

    /**
     * Контроллер обработки формы
     * @Route("/edit")
     */
    public function editAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if(empty($request->request->get('id'))) {       // Если пришла форма без id, значит создаем задачу
            $note = new Notes();
            if(!empty($request->request->get('parent'))) {
                $parent = $em->getRepository('George\TodoListBundle\Entity\Notes')->findOneBy(['id'=>$request->request->get('parent')]);
                if(empty($parent)) return $this->redirectToRoute('index_page');
                $note->setParent($parent);
            }
            $note->setIsActive(true);
        } else {                                        // Иначе достаем из базы нужную запись
            /** @var Notes $note */
            $note = $em->getRepository('George\TodoListBundle\Entity\Notes')->findOneBy(['id'=>$request->request->get('id')]);
            if(empty($note)) return $this->redirectToRoute('index_page');
        }

        // Здесь определяем какая кнопка нажата
        if(!is_null($request->request->get('save'))) {
            $note->setTitle($request->request->get('title'));
            $note->setText($request->request->get('text'));
            $em->persist($note);
            $em->flush();
        } elseif (!is_null($request->request->get('active'))) {
            $note->setIsActive(!$note->getIsActive());
            $em->persist($note);
            $em->flush();
            if($note->getIsActive() == false) {         // Подпроцесс завершения дочерних задач при закрытии задачи
                $repository = $em->getRepository('George\TodoListBundle\Entity\Notes');
                $topNode = MainHelper::findTopTree($note, $repository);
                MainHelper::setNonActive($topNode, $em );
                $em->flush();
            }
        } elseif (!is_null($request->request->get('remove'))) {
            $childs = MainHelper::getChilds($note, $em);
            foreach ($childs as $child) {
                $em->remove($child);
            }
            $em->flush();
        }
        return $this->redirectToRoute('index_page');
    }


    /**
     * Контроллер получения данных для дерева
     * @Route("/getIt/{filter}")
     */
    public function getItAction(Request $request, $filter='all' )
    {
        $em = $this->getDoctrine()->getManager();
        $criteria = [];
        switch ($filter) {
            case 'all':
                break;
            case 'active':
                $criteria['isActive'] = true;
                break;
            case 'complete':
                $criteria['isActive'] = false;
        }
        if($request->query->get('id') == '#') {
            $criteria['parent'] = null;
        } else {
            $criteria['parent'] = $request->query->get('id');
        }
        $notes = $em->getRepository('George\TodoListBundle\Entity\Notes')->findBy($criteria);

        foreach ($notes as $note) {
            $childNodes = $em->getRepository('George\TodoListBundle\Entity\Notes')->findBy(['parent' => $note->getId()]);
            $noteHTML[] = [
                'title' => $note->getTitle(),
                'id'    => $note->getId(),
                'isActive' => $note->getIsActive(),
                'isParent' => empty($childNodes) ? false : true
            ];
        }

        return $this->render('TodoListBundle:Default:tree.html.twig', array(
            'notes' => $noteHTML
        ));
    }

    /**
     * Получение формы записки с определенным id
     * @Route("/getNote/{id}")
     */
    public function getAction($id=null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $note = $em->getRepository('George\TodoListBundle\Entity\Notes')->findOneBy(['id'=>$id]);
        if(empty($id)) {
            $note = new Notes();
        }
        return $this->render('TodoListBundle:Default:form.html.twig', array(
            'id'        => $note->getId(),
            'title'     => $note->getTitle(),
            'text'      => $note->getText(),
            'active'    => $note->getIsActive(),
            'parent'    => !empty($note->getParent()) ? $note->getParent()->getId() : '#',
            'notnew'    => true
        ));
    }

    /**
     * Получить форму новой задачи с определенным id родителя
     * @Route("/getNewNote/{parentID}")
     */
    public function getNewNoteAction($parentID)
    {
        $note = new Notes();

        return $this->render('TodoListBundle:Default:form.html.twig', array(
            'id'        => $note->getId(),
            'title'     => $note->getTitle(),
            'text'      => $note->getText(),
            'active'    => $note->getIsActive(),
            'parent'    => $parentID,
            'notnew'    => false
        ));
    }

    /**
     * Получить форму новой корневой задачи
     * @Route("/getNewNote")
     */
    public function getNewNoteEmptyAction()
    {
        return $this->getNewNoteAction(null);
    }
}
