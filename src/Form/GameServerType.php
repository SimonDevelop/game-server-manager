<?php

namespace App\Form;

use App\Entity\GameServer;
use App\Entity\Server;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('commandStart')
            ->add('commandStop')
            ->add('commandUpdate')
            ->add('path')
            ->add('gameType', ChoiceType::class, [
                "choices" => $this->getChoices()
            ])
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GameServer::class,
        ]);
    }

    private function getChoices()
    {
        $choices = GameServer::GAME_TYPE;
        $output = [];
        foreach ($choices as $k => $v) {
            $output[$v] = $k;
        }
        return $output;
    }
}
