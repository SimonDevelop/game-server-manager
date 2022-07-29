<?php
namespace App\Controller;

use App\Repository\GameServerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')")]
class HomeController extends AbstractController
{
    #@var GameServerRepository
    private $gameServerRepository;

    #@param GameServerRepository
    public function __construct(GameServerRepository $gameServerRepository)
    {
        $this->gameServerRepository = $gameServerRepository;
    }
        
    #[Route(path: '/', name: 'app_home')]
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        $user = $this->getUser();
        if (in_array('ROLE_USER', $user->getRoles())) {
            $gameServers = $this->gameServerRepository->findByUser($user->getId());
        } else {
            $gameServers = $this->gameServerRepository->findAll();
        }

        $serversOn = 0;
        $serversOff = 0;
        $serversStarting = 0;
        $serversStopping = 0;

        foreach ($gameServers as $v) {
            if ($v->getState() === 'On') {
                $serversOn++;
            } elseif ($v->getState() === 'Off') {
                $serversOff++;
            } elseif ($v->getState() === 'Starting') {
                $serversStarting++;
            } elseif ($v->getState() === 'Stopping') {
                $serversStopping++;
            }
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_PIE);

        $chart->setData([
            'labels' => ['Serveurs On', 'Serveurs Off', 'Serveurs Starting', 'Serveurs Stopping'],
            'datasets' => [
                [
                    'label' => 'Les serveurs de jeu',
                    'backgroundColor' => ['rgb(46, 184, 46)', 'rgb(255, 64, 0)', 'rgb(245, 245, 0)', 'rgb(255, 128, 0)'],
                    'borderColor' => ['rgb(46, 184, 46)', 'rgb(255, 64, 0)', 'rgb(245, 245, 0)', 'rgb(255, 128, 0)'],
                    'data' => [$serversOn, $serversOff, $serversStarting, $serversStopping],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $this->render("pages/home.html.twig", [
            'chart' => $chart,
        ]);
    }
}
