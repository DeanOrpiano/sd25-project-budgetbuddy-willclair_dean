<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\BudgetTransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        CategoryRepository $categoryRepository,
        BudgetTransactionRepository $budgetTransactionRepository
    ): Response
    {
        // Get active categories
        $categories = $categoryRepository->findBy([
            'status' => true
        ]);

        // Get all budget transactions
        $transactions = $budgetTransactionRepository->findAll();

        $budgets = [];
        $todayIncome = 0;

        $today = (new \DateTimeImmutable(
            'today',
            new \DateTimeZone('Europe/Amsterdam')
        ))->format('Y-m-d');

        foreach ($transactions as $transaction) {
            $categoryId = $transaction->getCategory()->getId();

            // Convert euros to cents
            $amount = (int) round(
                (float) $transaction->getAmount() * 100
            );

            if (!isset($budgets[$categoryId])) {
                $budgets[$categoryId] = 0;
            }

            // Add money to the category
            $budgets[$categoryId] += $amount;

            // Check if transaction was created today
            if (
                $transaction->getCreatedAt() !== null &&
                $transaction->getCreatedAt()->format('Y-m-d') === $today
            ) {
                $todayIncome += $amount;
            }
        }

        // Calculate total budget of active categories
        $totalBudget = 0;

        foreach ($categories as $category) {
            $totalBudget += $budgets[$category->getId()] ?? 0;
        }

        // Convert cents back to euros
        foreach ($budgets as $id => $amount) {
            $budgets[$id] = $amount / 100;
        }

        return $this->render('dashboard/index.html.twig', [
            'categories' => $categories,
            'budgets' => $budgets,
            'totalBudget' => $totalBudget / 100,
            'todayIncome' => $todayIncome / 100
        ]);
    }
}
