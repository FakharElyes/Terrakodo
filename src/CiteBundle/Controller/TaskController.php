<?php

namespace CiteBundle\Controller;

use CiteBundle\Entity\Task;
use CiteBundle\Form\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class TaskController extends Controller
{
//-----------------------------------------------------Affichage Task----------------------------------------------
    public function ReadTaskAction(){
        $event=$this->getDoctrine()->getRepository(Task::class)->findAll();
        //add the list of clubs to the render function as input to base
        return $this->render('@Cite/Tasks/ReadTasks.html.twig',array('events'=>$event));
    }


//--------------------------------------------------------Ajout Task----------------------------------------------
    public function CreateTaskAction(Request $request){
        $event = new Task;
        $form = $this->createForm(TaskType::class, $event);
        $form = $form->handleRequest($request);
        if ($form ->isSubmitted() and $form->isValid()){
            $file = $event->getImage();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('image_directory'),$fileName);
            $event->setImage($fileName);
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute('CategorieRead');
        }
        return $this->render('@Cite/Tasks/createTask.html.twig',array('f'=> $form->createView()));
    }

//-----------------------------------------------------Supression Task----------------------------------------------
    public function DeleteTaskAction($id){
        $em = $this->getDoctrine()->getManager();
        $event=$em->getRepository(Task::class)->find($id);
        $em->remove($event);
        $em->flush();
        return $this->redirectToRoute("CategorieRead");
    }

//-----------------------------------------------------Modification Task----------------------------------------------
    public function UpdateTaskAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository(Task::class)->find($id);
        $form = $this->createForm(TaskType::class, $event);
        $form = $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute('CategorieRead');
        }

        return $this->render('@Cite/Tasks/updateTask.html.twig', array('f' => $form->createView()));
    }


//------------------------------------- Export XL -----------------------------------------------------

    public function index()
    {
        $spreadsheet = new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hello World !');
        $sheet->setTitle("My First Worksheet");

        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);

        // Create a Temporary file in the system
        $fileName = 'my_first_excel_symfony4.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);

        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }






}
