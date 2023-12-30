<?php

namespace App\Form;

use App\Entity\GameServer;
use App\Entity\Server;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('commandStart')
            ->add('commandStop')
            ->add('commandUpdate')
            ->add('commandCustomInternal')
            ->add('path')
            ->add('server', EntityType::class, [
                "class" => Server::class,
                "required" => true,
                "choice_label" => "name",
                "multiple" => false,
            ])
            ->add('users', UserAutocompleteField::class, [
                "by_reference" => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GameServer::class,
        ]);
    }
}
