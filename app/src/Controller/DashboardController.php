<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\BudgetTransactionRepository;
use App\Repository\ExpenseTransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        CategoryRepository $categoryRepository,
        BudgetTransactionRepository $budgetRepository,
        ExpenseTransactionRepository $expenseRepository
    ): Response
    {
        // Get active categories
        $categories = $categoryRepository->findBy([
            'status' => true
        ]);

        $budgetTransactions = $budgetRepository->findAll();
        $expenseTransactions = $expenseRepository->findAll();

        $budgets = [];
        $expenses = [];
        $todayIncome = 0;
        $todayExpenses = 0;


        $today = (new \DateTimeImmutable())->format('Y-m-d');

        // Calculate budgets
        foreach ($budgetTransactions as $transaction) {
            $categoryId = $transaction->getCategory()->getId();
            $amount = (int) round((float) $transaction->getAmount() * 100);

            $budgets[$categoryId] = ($budgets[$categoryId] ?? 0) + $amount;

            if ($transaction->getCreatedAt()?->format('Y-m-d') === $today) {
                $todayIncome += $amount;
            }
        }

        // Calculate expenses
        foreach ($expenseTransactions as $transaction) {
            $categoryId = $transaction->getCategory()->getId();
            $amount = (int) round((float) $transaction->getAmount() * 100);

            $expenses[$categoryId] = ($expenses[$categoryId] ?? 0) + $amount;

            
// Check if the expense was made today
if ($transaction->getCreatedAt()?->format('Y-m-d') === $today) {
    $todayExpenses += $amount;
}

        }

        // Totals for active categories
        $totalBudget = 0;
        $totalExpenses = 0;

        foreach ($categories as $category) {
            $id = $category->getId();

            $totalBudget += $budgets[$id] ?? 0;
            $totalExpenses += $expenses[$id] ?? 0;
        }

        // Convert cents to euros
        foreach ($budgets as $id => $amount) {
            $budgets[$id] = $amount / 100;
        }

        foreach ($expenses as $id => $amount) {
            $expenses[$id] = $amount / 100;
        }

        return $this->render('dashboard/index.html.twig', [
            'todayExpenses' => $todayExpenses / 100,
            'categories' => $categories,
            'budgets' => $budgets,
            'expenses' => $expenses,
            'totalBudget' => $totalBudget / 100,
            'totalExpenses' => $totalExpenses / 100,
            'remainingBudget' => ($totalBudget - $totalExpenses) / 100,
            'todayIncome' => $todayIncome / 100
        ]);
    }
}
