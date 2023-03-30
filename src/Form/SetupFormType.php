<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class SetupFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'invalid_message' => 'The password fields do not match.',
                'options'         => ['attr' => ['class' => 'form-control mb-4']],
                'required'        => false,
                'first_options'   => ['label' => 'Password'],
                'second_options'  => ['label' => 'Confirm password'],
                'constraints'     => [
                    new Length([
                        'min'        => 6,
                        'minMessage' => 'Your password must be at least {{ limit }} characters',
                        'max'        => 4096,
                        'maxMessage' => 'Your password must not exceed {{ limit }} characters',
                    ]),
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
