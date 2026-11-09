<?php

namespace App\Controller;

use App\Repository\ExpenseTransactionRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExpenseReportController extends AbstractController
{
    #[Route('/expense-report', name: 'app_expense_report')]
    public function index(
        Request $request,
        ExpenseTransactionRepository $expenseRepository,
        CategoryRepository $categoryRepository
    ): Response
    {
        // Categories for the dropdown
        $categories = $categoryRepository->findAll();

        // Read the selected filters
        $start = $request->query->get('start', date('Y-m-01'));
        $end = $request->query->get('end', date('Y-m-d'));
        $categoryId = $request->query->getInt('category', 0);

        $transactions = [];
        $totalCents = 0;
        $error = null;

        // Convert text dates into PHP dates
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
            // Find expenses within the chosen dates
            $query = $expenseRepository->createQueryBuilder('e')
                ->join('e.category', 'c')
                ->addSelect('c')
                ->where('e.createdAt >= :start')
                ->andWhere('e.createdAt < :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate->modify('+1 day'))
                ->orderBy('e.createdAt', 'ASC')
                ->addOrderBy('e.id', 'ASC');

            // Filter by category if selected
            if ($categoryId > 0) {
                $query->andWhere('c.id = :categoryId')
                    ->setParameter('categoryId', $categoryId);
            }

            $transactions = $query->getQuery()->getResult();

            // Calculate total expenses
            foreach ($transactions as $transaction) {
                $totalCents += (int) round(
                    (float) $transaction->getAmount() * 100
                );
            }
        }

        return $this->render('report/expense.html.twig', [
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
