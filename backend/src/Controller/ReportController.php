<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\MediaRepository;
use App\Service\ElasticsearchClient;

class ReportController extends AbstractController
{
    private $mediaRepository;
    private $elasticsearchClient;

    public function __construct(MediaRepository $mediaRepository, ElasticsearchClient $elasticsearchClient) {
        $this->mediaRepository = $mediaRepository;
        $this->elasticsearchClient = $elasticsearchClient;
    }

    #[Route('/report/storage-per-user', name: 'storage-per-user', methods: ['GET'])]
    public function storagePerUser(): JsonResponse
    {
        $storageData = $this->mediaRepository->getTotalStoragePerUser();

        return $this->json($storageData, Response::HTTP_OK);
    }

    #[Route('/report/file-types-per-user', name: 'file-type-per-user', methods: ['GET'])]
    public function storagePerFileType(): JsonResponse
    {
        $data = $this->mediaRepository->getFileTypesPerUser();

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/report/activity-logs', name: 'get-activity-logs', methods: ['GET'])]
    public function getActivityLogs(Request $request): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 10);

        $from = ($page - 1) * $perPage;


        $userId = $request->query->get('user_id');
        $params = [
            'index' => 'activity_logs',
            'body' => [
                '_source' => ['type', 'timestamp', 'data'],
                'query' => $userId ?
                    ['bool' => ['must' =>[ ['match' => ['data.user_id' => $userId] ] ] ] ] :
                    ['match_all' => new \stdClass()],
                'size' => $perPage,
                'from' => $from,
            ]
        ];

        $elasticsearchResponse = $this->elasticsearchClient->getClient()->search($params);
        $data = array_map(function ($hit) {
            return $hit['_source'];
        }, $elasticsearchResponse['hits']['hits']);

        $totalHits = $elasticsearchResponse['hits']['total']['value'];

        $response = [
            'data' => $data,
            'pagination' => [
                'total' => $totalHits,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($totalHits / $perPage),
        ]];


        return $this->json($response, Response::HTTP_OK);
    }


}
