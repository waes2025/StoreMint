<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Modules\Cart\Models\Transaction;
use Illuminate\Http\Request;

/**
 * Example controller demonstrating business context selection and transaction management
 * This shows best practices for using the global business selection functions
 */
class BusinessContextController extends Controller
{
    /**
     * Show the business and location selection page
     */
    public function selectBusiness()
    {
        return inertia('Business/Select', [
            'availableBusinesses' => availableBusinesses(),
            'currentContext' => businessContext(),
        ]);
    }

    /**
     * Store selected business in session
     */
    public function setBusiness(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:business,id',
        ]);

        $business = setCurrentBusiness($request->business_id);

        return response()->json([
            'message' => 'Business selected successfully',
            'business' => $business,
            'context' => businessContext(),
        ]);
    }

    /**
     * Show location selection for current business
     */
    public function selectLocation()
    {
        if (! hasCurrentBusiness()) {
            return response()->json([
                'error' => 'Please select a business first',
            ], 422);
        }

        return inertia('Business/SelectLocation', [
            'availableLocations' => availableLocations(),
            'currentBusiness' => currentBusiness(),
            'currentContext' => businessContext(),
        ]);
    }

    /**
     * Store selected location in session
     */
    public function setLocation(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:business_locations,id',
        ]);

        if (! hasCurrentBusiness()) {
            return response()->json([
                'error' => 'Business context not set',
            ], 422);
        }

        try {
            $location = setCurrentLocation($request->location_id);

            return response()->json([
                'message' => 'Location selected successfully',
                'location' => $location,
                'context' => businessContext(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get current context
     */
    public function getContext()
    {
        return response()->json(businessContext());
    }

    /**
     * Clear business context (usually called on logout)
     */
    public function clearContext()
    {
        clearBusinessContext();

        return response()->json([
            'message' => 'Business context cleared',
        ]);
    }

    /**
     * Example: Create a transaction with current business context
     */
    public function createTransaction(Request $request)
    {
        // Validate business and location are selected
        if (! currentBusinessId() || ! currentLocationId()) {
            return response()->json([
                'error' => 'Please select business and location',
                'available_businesses' => availableBusinesses(),
            ], 422);
        }

        $request->validate([
            'contact_id' => 'nullable|exists:contacts,id',
            'type' => 'required|in:purchase,sale,return,expense',
            'status' => 'required|in:draft,pending,completed',
            'total_before_tax' => 'required|numeric|min:0',
        ]);

        try {
            $transaction = Transaction::create([
                'business_id' => currentBusinessId(),
                'location_id' => currentLocationId(),
                'created_by' => auth()->id(),
                'type' => $request->type,
                'status' => $request->status,
                'payment_status' => 'pending',
                'contact_id' => $request->contact_id,
                'total_before_tax' => $request->total_before_tax,
                'transaction_date' => now(),
                'invoice_no' => $this->generateInvoiceNumber(),
                'ref_no' => $this->generateRefNumber(),
            ]);

            return response()->json([
                'message' => 'Transaction created successfully',
                'transaction' => $transaction,
                'context' => businessContext(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create transaction: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Example: Get transactions for current business/location context
     */
    public function getTransactions(Request $request)
    {
        if (! currentBusinessId()) {
            return response()->json([
                'error' => 'Please select a business first',
            ], 422);
        }

        $query = Transaction::where('business_id', currentBusinessId());

        if (currentLocationId()) {
            $query->where('location_id', currentLocationId());
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transactions = $query
            ->with(['business', 'location', 'user'])
            ->orderByDesc('transaction_date')
            ->paginate();

        return response()->json([
            'transactions' => $transactions,
            'context' => businessContext(),
        ]);
    }

    /**
     * Helper: Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        return 'INV-'.currentBusinessId().'-'.now()->format('Ymd').'-'.rand(10000, 99999);
    }

    /**
     * Helper: Generate unique reference number
     */
    private function generateRefNumber(): string
    {
        return 'REF-'.strtoupper(bin2hex(random_bytes(4)));
    }
}
