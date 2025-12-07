# Keyboard-Driven POS Flow

## Overview
The POS system now supports complete keyboard-only operation for seamless, zero-mouse sales processing.

## Complete Flow Sequence

### 1. Product Search → Enter
- **Action**: Type product name, brand, or generic name in search box
- **Shortcut**: Press `F1` to focus search anytime
- **Navigation**: 
  - `↑` / `↓` arrows to navigate results
  - `Enter` to select highlighted product
  - `Esc` to clear results

### 2. Quantity Input → Enter
- **Auto-focus**: Quantity field automatically focused when modal opens
- **Default**: 1 (pre-filled)
- **Action**: Type quantity and press `Enter`
- **Validation**: Cannot exceed available stock
- **Skip**: Press `Enter` to move to next field

### 3. Batch Selection (if needed) → Enter
- **Conditional**: Only shown if product has multiple batches
- **Action**: Use `↑` / `↓` to select batch, press `Enter`
- **Skip**: If no batches, auto-skipped to Rate field
- **Optional**: Can leave empty

### 4. Rate/Price → Enter
- **Auto-filled**: Default product price pre-populated
- **Action**: Modify if needed, press `Enter` to add to cart
- **Live Total**: Total (Qty × Rate) updates automatically
- **Final Step**: Press `Enter` to confirm and add to cart

### 5. Back to Product Search
- **Auto-return**: Focus automatically returns to search box
- **Continue**: Start typing next product immediately
- **No delays**: Seamless flow for next item

## Modal Keyboard Shortcuts

### Within Product Details Modal
- `Enter` - Move to next field / Confirm at Rate field
- `Esc` - Cancel and close modal (return to search)
- `Tab` - Navigate between fields (alternative to Enter)

## Global Keyboard Shortcuts

### Function Keys
- `F1` - Focus product search
- `F2` - Focus customer name
- `F3` - Hold current sale
- `F4` - Recall held sales
- `F7` - Focus discount input
- `F8` - Remove selected cart item

### Control Keys
- `Ctrl + Enter` - Complete sale / Show payment modal
- `Ctrl + Delete` - Clear cart
- `Ctrl + K` - Focus product search
- `Ctrl + H` - Hold invoice
- `Ctrl + B` - Open barcode scanner

### Navigation Keys
- `Esc` - Close any open modal
- `↑` / `↓` - Navigate search results or cart items
- `Enter` - Select/Confirm
- `Delete` - Remove cart item (when focused)

## Features

### Zero Mouse Required
- ✅ Product search and selection
- ✅ Quantity, batch, and price entry
- ✅ Cart management
- ✅ Payment processing
- ✅ Complete sale workflow

### Smart Flow
- Auto-focus on quantity after product selection
- Skip batch field if not applicable
- Live total calculation
- Instant return to search after adding item
- Stock validation before adding

### User-Friendly
- Visual feedback on focused elements
- Clear button labels with shortcuts
- Modal escape routes (Cancel/ESC)
- No dead ends - always a way back

## Typical Workflow Example

```
1. F1 (or click search) → Type "paracetamol"
2. ↓ ↓ → Select "Paracetamol 500mg"
3. Enter → Modal opens, focus on Qty
4. Type "2" → Enter
5. [Batch skipped - no batches]
6. Rate shows "50.00" → Enter
7. Product added to cart!
8. Focus returns to search automatically
9. Type next product...
```

## Advanced Usage

### Quick Add (No modifications)
- Search → Enter → Enter → Enter → Enter
- Uses default qty=1 and product price

### Modify Price
- Search → Enter → Enter (skip qty) → Type new price → Enter

### With Batch Selection
- Search → Enter → Qty → Enter → Select Batch → Enter → Rate → Enter

### Cancel at Any Point
- Press `Esc` to cancel and return to search

## Technical Details

### Modal ID
`#productDetailsModal`

### Input IDs
- `#modalQuantity` - Quantity input
- `#modalBatch` - Batch selection dropdown
- `#modalRate` - Price/Rate input

### Functions
- `showProductDetailsModal(product)` - Open modal
- `hideProductDetailsModal()` - Close modal
- `confirmProductDetails()` - Add to cart
- `updateModalTotal()` - Recalculate total

### Integration
Works seamlessly with existing `POSCart` class via:
```javascript
window.posCart.addToCart({
    productId, productName, productPrice, 
    productStock, productUnit, quantity, batch
});
```

## Accessibility

- Full keyboard navigation
- Focus trap within modal
- ARIA attributes for screen readers
- Visual focus indicators
- Logical tab order

## Performance

- Debounced search (120ms)
- Cached search results
- No page reloads
- Instant modal display
- Smooth animations

---

**Cashier Efficiency**: Complete sale processing in seconds without touching the mouse!
