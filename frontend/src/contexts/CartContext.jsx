import { createContext, useContext, useState, useEffect } from 'react';
import { api } from '../api/client.js';

const CartContext = createContext();

export function CartProvider({ children }) {
  const [cartCount, setCartCount] = useState(0);
  const [user, setUser] = useState(null);

  const refreshCartCount = async () => {
    try {
      const res = await api.get('/me');
      const u = res.data.user;
      setUser(u);
      if (u?.role === 'customer') {
        const cartRes = await api.get('/cart');
        const items = cartRes.data.items || [];
        const totalQty = items.reduce(
          (sum, item) => sum + (item.quantity || 0),
          0,
        );
        setCartCount(totalQty);
      } else {
        setCartCount(0);
      }
    } catch {
      setUser(null);
      setCartCount(0);
    }
  };

  useEffect(() => {
    refreshCartCount();
  }, []);

  return (
    <CartContext.Provider value={{ cartCount, refreshCartCount }}>
      {children}
    </CartContext.Provider>
  );
}

export function useCart() {
  return useContext(CartContext);
}

