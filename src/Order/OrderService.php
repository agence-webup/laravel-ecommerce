<?php

class OrderService {
    public function checkout(Cart $cart)
    {
        $this->validate($cart);
        $order = $this->makeOrder($cart);
        $this->orderRepository->save($order);

        // to resgister order into ERP
        event(new OrderCreated($order));
    }
}
