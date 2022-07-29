<?php
namespace App\Controller;

use App\Repository\GameServerRepository;
use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\BrowserKit\Request;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')")]
class HomeController extends AbstractController
{
    #@var GameServerRepository
    private $gameServerRepository;

    #@var LogRepository
    private $logRepository;

    #@param GameServerRepository
    #@param LogRepository
    public function __construct(GameServerRepository $gameServerRepository, LogRepository $logRepository)
    {
        $this->gameServerRepository = $gameServerRepository;
        $this->logRepository        = $logRepository;
    }
        
    #[Route(path: '/', name: 'app_home')]
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        $user = $this->getUser();
        if (in_array('ROLE_USER', $user->getRoles())) {
            $gameServers = $this->gameServerRepository->findByUser($user->getId());
            $logs        = $this->logRepository->getLogsByUser($user->getId(), 8);
        } else {
            $gameServers = $this->gameServerRepository->findAll();
            $logs        = $this->logRepository->getLogs(8);
        }

        $serversOn   = 0;
        $serversOff  = 0;
        $serverOther = 0;

        foreach ($gameServers as $v) {
            if ($v->getState() === 'On') {
                $serversOn++;
            } elseif ($v->getState() === 'Off') {
                $serversOff++;
            } else {
                $serverOther++;
            }
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_PIE);

        $chart->setData([
            'labels'   => ['Serveurs On', 'Serveurs Off', 'Autres'],
            'datasets' => [
                [
                    'label'           => 'Les serveurs de jeu',
                    'backgroundColor' => ['rgb(46, 184, 46)', 'rgb(255, 64, 0)', 'rgb(255, 153, 51)'],
                    'borderColor'     => ['rgb(46, 184, 46)', 'rgb(255, 64, 0)', 'rgb(255, 153, 51)'],
                    'data'            => [$serversOn, $serversOff, $serverOther],
                ],
            ],
        ]);

        return $this->render("pages/home.html.twig", [
            'chart' => $chart,
            'logs'  => $logs,
        ]);
    }

    #[Route(path: '/logs/{page}', name: 'logs_index', methods: ['GET'])]
    public function logs_page(int $page): Response
    {
        if ($page <= 0) {
            return $this->redirectToRoute('logs_index', ['page' => 1]);
        }

        $limit = 20;
        if ($page == 1) {
            $position = 0;
        } else {
            $position = ($page-1)*$limit;
        }

        $logsTotal = $this->logRepository->getLogsPage($position, $limit);
        $nbPage = $logsTotal/$limit;
        if ($nbPage > intval($nbPage)) {
            $nbPage = intval($nbPage)+1;
        }

        if ($nbPage < $page && $nbPage != 0) {
            return $this->redirectToRoute('logs_index', ['page' => $nbPage]);
        }

        $logs = $this->logRepository->getLogsWithPosition($position, $limit);
        $params = compact("logs", "logsTotal", "page", "nbPage");

        return $this->render("pages/logs.html.twig", $params);
    }
}
