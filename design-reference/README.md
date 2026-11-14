# Campus Cafeteria — Interactive Prototype

## Run in VS Code
1. Extract the `campus-cafeteria-v2.zip` file.
2. Open the extracted `campus-cafeteria-v2` folder in Visual Studio Code.
3. Open `index.html` in a browser, or install the VS Code extension **Live Server**.
4. If using Live Server, right-click `index.html` → **Open with Live Server**.

## Files
- `index.html` — app structure
- `css/style.css` — responsive styling and colour system
- `js/app.js` — navigation, profiles, menu, cart, checkout and demo data

## Features to test
- Switch Student, Cafeteria Staff, Cafeteria Manager and System Administrator profiles from the left sidebar.
- Each profile gets a different navigation menu and dashboard.
- Drag navigation tabs up or down to reorder them. The order is saved per profile in browser local storage.
- Browse JMI, Salad Bar, CP Fried Chicken, Tea Stall and Juice Bar.
- Add products to cart, change quantities, checkout, and place sample orders.
- Switch to Staff to update order statuses.
- Switch to Manager to review and edit sample menu prices / availability.
- Switch to Administrator to review sample users.
- Edit the current demo profile.
- Reset demo data from the bottom of the sidebar.

## Important
- This is a front-end prototype only. It does not include Laravel, PHP, a database, real authentication, payment processing, email/SMS, or live cafeteria inventory.
- All orders, profiles and menu data are demonstration data saved locally in the browser. Use the Reset demo data option to restore the initial state.
- Menu items and prices are illustrative examples in BDT, not confirmed official campus cafeteria prices.
- No real payment is taken. bKash and Nagad choices are visual demo options only.
- Google Fonts is used when internet access is available; system fonts are used as fallback.
