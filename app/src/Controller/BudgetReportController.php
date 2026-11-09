<?php

namespace App\Controller;

use App\Repository\BudgetTransactionRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BudgetReportController extends AbstractController
{
    #[Route('/budget-report', name: 'app_budget_report')]
    public function index(
        Request $request,
        BudgetTransactionRepository $budgetRepository,
        CategoryRepository $categoryRepository
    ): Response
    {
        // Get all categories for the dropdown
        $categories = $categoryRepository->findAll();

        // Get filters from the URL
        $start = $request->query->get('start', date('Y-m-01'));
        $end = $request->query->get('end', date('Y-m-d'));
        $categoryId = $request->query->getInt('category', 0);

        $transactions = [];
        $totalCents = 0;
        $error = null;

        // Convert the entered dates
        $startDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $start);
        $endDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $end);

        if (
            !$startDate ||
            !$endDate ||
            $startDate->format('Y-m-d') !== $start ||
            $endDate->format('Y-m-d') !== $end ||
            $startDate > $endDate
        ) {
            $error = 'Vul een geldige periode in.';
        } else {
            // Search budget transactions
            $query = $budgetRepository->createQueryBuilder('b')
                ->join('b.category', 'c')
                ->addSelect('c')
                ->where('b.createdAt >= :start')
                ->andWhere('b.createdAt < :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate->modify('+1 day'))
                ->orderBy('b.createdAt', 'ASC');

            // Filter by category when selected
            if ($categoryId > 0) {
                $query->andWhere('c.id = :categoryId')
                    ->setParameter('categoryId', $categoryId);
            }

            $transactions = $query->getQuery()->getResult();

            // Calculate total money
            foreach ($transactions as $transaction) {
                $totalCents += (int) round(
                    (float) $transaction->getAmount() * 100
                );
            }
        }

        return $this->render('report/budget.html.twig', [
            'categories' => $categories,
            'transactions' => $transactions,
            'start' => $start,
            'end' => $end,
            'categoryId' => $categoryId,
            'total' => $totalCents / 100,
            'error' => $error
        ]);
    }
}
