<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;
use App\Entity\Permission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
             ->add('plainPassword', PasswordType::class, [
                'required'=> false
             ])
            ->add('email', EmailType::class)
            ->add('displayName', TextType::class, [
                'required'=> false
            ])
            ->add('activate', ChoiceType::class, [
                'choices'=> [
                    'activated'=> 'activated',
                    'deactivated'=>'deactivated'
                ]
            ])
            ->add('permissions',EntityType::class,[
                'class' => Permission::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
            ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
