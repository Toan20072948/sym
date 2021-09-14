<?php


namespace App\Controller;


use App\Entity\ApplicationGrant;
use App\Entity\User;
use App\Form\GrantType;
use App\Form\UserType;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AdminController extends AbstractController
{
    /**
     * @Route("/admin/dashboard", name="admin_dashboard")
     */
    public function dashboard() {
        return $this->render('admin/dashboard.html.twig');
    }

     /**
     * @Route("/admin/users", name="admin_user_list")
     */
    public function userList(Request $request, PaginatorInterface $paginator) {  
    
        $users = $this->getDoctrine()
                ->getRepository(User::class)
                ->findAll();
    
        $users = $paginator->paginate(
                $users, /* query NOT result */
                $request->query->getInt('page', 1),
                3/*limit per page*/
        );
       
            return $this->render('admin/userList.html.twig', [
                'users'=> $users,
                ]);  
    }

    /**
     * @Route("/admin/userSearch", name="admin_user_search")
     */
    public function userSearch(Request $request) {
        $limit = $request->request->get('limit');;
        $page = 1;
          $searchWord = $request->request->get('searchWord');
          $found = $this->getDoctrine()
          ->getRepository(User::class)
          ->createQueryBuilder('u')
            ->where('u.username like :query')
            ->setParameter('query', '%'.$searchWord.'%')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getQuery()
            ->getResult();
        
        return $this->json(['found' => $found]);
    }

    /**
     * @Route("/admin/createUser", name="admin_createUser")
     */
    public function createUser(Request $request, UserPasswordEncoderInterface $passwordEncoder){
        $user = new User();
        $form = $this->createForm(UserType::class, $user)
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'save'],
            ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            /**
             * @var $user User
             */
            // encoding the plainPassword
            $encodedPassword = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encodedPassword);
            //
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('admin/createUser.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/users/{id}", name="admin_user_show")
     */
    public function userShow($id) {
        $user = $this->getDoctrine()->getRepository(User::class)
            ->find($id);
        if($user === null) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/admin/editUser/{id}", name="admin_user_edit")
     */
    public function editUser(Request $request, $id){

        $user = $this->getDoctrine()
        ->getRepository(User::class)
        ->find($id);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
//            dd($data);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('admin/editUser.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/delete/{id}", name="admin_delete_user")
     * @Method({"DELETE"})
     */
    public function deleteUser($id) {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
  
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin_user_list');
      }

      /**
       * @Route("/admin/createGrant", name="admin_create_grant")
       */

      public function createGrant(Request $request){
        $grant = new ApplicationGrant();
        $form =$this->createForm(GrantType::class, $grant);

          $form->handleRequest($request);
          if ($form->isSubmitted() && $form->isValid()) {
              $task = $form->getData();
              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->persist($grant);
              $entityManager->flush();

              return $this->redirectToRoute('admin_dashboard');
          }
          return $this->render('admin/createGrant.html.twig',[
              'form' => $form->createView(),
          ]);
      }

      /**
       * @Route("/admin/grants", name="admin_list_grants")
       */
      public function listGrant(Request $request, PaginatorInterface $paginator){
          $grants = $this->getDoctrine()->getRepository(ApplicationGrant::class)
              ->findAll();

          $grants = $paginator->paginate(
              $grants, /* query NOT result */
              $request->query->getInt('page', 1),
              5/*limit per page*/
          );
          return $this->render ('admin/listGrant.html.twig',[
              'grants' => $grants,
          ]);
      }



}
