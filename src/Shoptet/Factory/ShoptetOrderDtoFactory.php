<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Factory;

use Cake\Chronos\Chronos;
use DateTime;
use Elasticr\ServiceBus\ServiceBus\Model\AddressDto;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;
use Elasticr\ServiceBus\Shoptet\Exception\OrderCreationException;

final class ShoptetOrderDtoFactory
{
    private AddressDtoFactory $addressFactory;

    private ShoptetOrderItemDtoFactory $orderItemFactory;

    private ShoptetPaymentMethodDtoFactory $paymentMethodDtoFactory;

    private ShoptetShippingMethodDtoFactory $shippingMethodDtoFactory;

    private OrderNoteDtoFactory $orderNoteDtoFactory;

    private OrderCouponDtoFactory $orderCouponDtoFactory;

    public function __construct(
        AddressDtoFactory $addressFactory,
        ShoptetOrderItemDtoFactory $orderItemFactory,
        ShoptetPaymentMethodDtoFactory $paymentMethodDtoFactory,
        ShoptetShippingMethodDtoFactory $shippingMethodDtoFactory,
        OrderNoteDtoFactory $orderNoteDtoFactory,
        OrderCouponDtoFactory $orderCouponDtoFactory
    ) {
        $this->addressFactory = $addressFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->paymentMethodDtoFactory = $paymentMethodDtoFactory;
        $this->shippingMethodDtoFactory = $shippingMethodDtoFactory;
        $this->orderNoteDtoFactory = $orderNoteDtoFactory;
        $this->orderCouponDtoFactory = $orderCouponDtoFactory;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromApiResponse(array $data): OrderDto
    {
        $billingAddressData = $data['billingAddress'];

        if ($billingAddressData === null) {
            throw new OrderCreationException('Billing address is required');
        }

        $billingAddressData['email'] = $data['email'];
        $billingAddressData['phone'] = $data['phone'];

        /** @var AddressDto $billingAddress */
        $billingAddress = $this->addressFactory->createFromApiResponse($billingAddressData);

        $shippingPriceData = [];
        $paymentPriceData = [];
        $items = [];
        $coupon = null;
        foreach ($data['items'] as $item) {
            if ($item['itemType'] === 'discount-coupon' || $item['itemType'] === 'volume-discount') {
                $coupon = $this->orderCouponDtoFactory->createFromApiResponse($item);
                continue;
            }

            if ($item['itemType'] === 'billing') {
                $paymentPriceData = $item['itemPrice'];
            } elseif ($item['itemType'] === 'shipping') {
                $shippingPriceData = $item['itemPrice'];
            } else {
                $items[] = $this->orderItemFactory->createFromApiResponse($item);
            }
        }

        $deliveryAddress = $this->addressFactory->createFromApiResponse($data['deliveryAddress']);

        if (!$this->shippingAddressIsEmpty($data)) {
            $deliveryAddressData = $billingAddressData;
            $deliveryAddressData['street'] = $data['shippingDetails']['street'];
            $deliveryAddressData['houseNumber'] = null;
            $deliveryAddressData['company'] = null;
            $deliveryAddressData['city'] = $data['shippingDetails']['city'];
            $deliveryAddressData['zip'] = $data['shippingDetails']['zipCode'];
            $deliveryAddressData['countryCode'] = $data['shippingDetails']['countryCode'];

            $deliveryAddress = $this->addressFactory->createFromApiResponse($deliveryAddressData);
        }

        return new OrderDto(
            0,
            $data['code'],
            $billingAddress,
            $deliveryAddress,
            $this->paymentMethodDtoFactory->createFromApiResponse($data['paymentMethod'], $paymentPriceData),
            $this->shippingMethodDtoFactory->createFromApiResponse($data['shipping'], $shippingPriceData, $data['shippingDetails']['branchId'] ?? null),
            (string) $data['status']['id'],
            $data['paid'] ? 'zaplaceno' : 'nezaplaceno',
            (float) $data['price']['toPay'],
            (float) $data['price']['withoutVat'],
            $data['price']['currencyCode'],
            $items,
            Chronos::createFromFormat(DateTime::ATOM, $data['creationTime']),
            $this->orderNoteDtoFactory->createFromApiResponse($data['notes'] ?? []),
            $coupon
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function shippingAddressIsEmpty(array $data): bool
    {
        if (!array_key_exists('shippingDetails', $data) || $data['shippingDetails'] === null) {
            return true;
        }

        $shippingDetails = $data['shippingDetails'];

        return $shippingDetails['street'] === null || $shippingDetails['city'] === null || $shippingDetails['zipCode'] === null;
    }
}
