<?php

namespace George\TodoListBundle\Helpers;

use George\TodoListBundle\Entity\Notes;


class MainHelper
{
    /**
     * Возвращает список дочерних задач
     * @param Notes $note
     * @param $em
     * @return array
     */
    static public function getChilds($note, $em ) {
        $childNotes = $em->getRepository('George\TodoListBundle\Entity\Notes')->findBy([ 'parent' => $note ]);
        $stack = [];
        if(!empty($childNotes)) {
            foreach ($childNotes as $childNote) {
                $stack = $stack + MainHelper::getChilds($childNote, $em);
            }
        }
        $stack[] = $note;
        return $stack;
    }

    /**
     * Устанавливает архивными все дочерние задачи
     * @param Notes $note
     * @param $em
     */
    static public function setNonActive($note, $em ) {
        $childNotes = $em->getRepository('George\TodoListBundle\Entity\Notes')->findBy([ 'parent' => $note ]);
        if(!empty($childNotes)) {
            foreach ($childNotes as $childNote) {
                MainHelper::setNonActive($childNote, $em);
            }
        }
        $note->setIsActive(false);
        $em->persist($note);
        return;
    }

    /**
     * Находит верхнюю ноду, с которой можно начать закрывать дочерние задачи
     * @param Notes $note
     * @param \Doctrine\ORM\EntityRepository $repository
     * @return Notes
     */
    static public function findTopTree($note, $repository ) {
        if(is_null($note->getParent())) return $note;
        $notArchivedTasks = $repository->findBy([   'parent' => $note->getParent(),
            'isActive' => true ]);
        if(empty($notArchivedTasks)) {
            return MainHelper::findTopTree($note->getParent(), $repository);
        }
        else {
            return $note;
        }
    }
}
