<?php

/*
 * The MIT License
 *
 * Copyright (c) 2025 "YooMoney", NBСO LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace YooKassa\Model\Receipt;

use YooKassa\Common\AbstractEnum;

/**
 * Признак предмета расчета передается в параметре `payment_subject`.
 */
class PaymentSubject extends AbstractEnum
{
    /** Товар */
    const COMMODITY = 'commodity';
    /** Подакцизный товар */
    const EXCISE = 'excise';
    /** Работа */
    const JOB = 'job';
    /** Услуга */
    const SERVICE = 'service';
    /** Ставка в азартной игре */
    const GAMBLING_BET = 'gambling_bet';
    /** Выигрыш азартной игры */
    const GAMBLING_PRIZE = 'gambling_prize';
    /** Лотерейный билет */
    const LOTTERY = 'lottery';
    /** Выигрыш в лотерею */
    const LOTTERY_PRIZE = 'lottery_prize';
    /** Результаты интеллектуальной деятельности */
    const INTELLECTUAL_ACTIVITY = 'intellectual_activity';
    /** Платеж */
    const PAYMENT = 'payment';
    /** Агентское вознаграждение */
    const AGENT_COMMISSION = 'agent_commission';
    /** Имущественное право */
    const PROPERTY_RIGHT = 'property_right';
    /** Внереализационный доход */
    const NON_OPERATING_GAIN = 'non_operating_gain';
    /** Страховой сбор */
    const INSURANCE_PREMIUM = 'insurance_premium';
    /** Торговый сбор */
    const SALES_TAX = 'sales_tax';
    /** Курортный сбор */
    const RESORT_FEE = 'resort_fee';
    /** Несколько вариантов */
    const COMPOSITE = 'composite';
    /** Другое */
    const ANOTHER = 'another';

    /** Выплата */
    const FINE = 'fine';
    /** Страховые взносы */
    const TAX = 'tax';
    /** Залог */
    const LIEN = 'lien';
    /** Расход */
    const COST = 'cost';
    /** Взносы на обязательное пенсионное страхование ИП */
    const PENSION_INSURANCE_WITHOUT_PAYOUTS = 'pension_insurance_without_payouts';
    /** Взносы на обязательное пенсионное страхование */
    const PENSION_INSURANCE_WITH_PAYOUTS = 'pension_insurance_with_payouts';
    /** Взносы на обязательное медицинское страхование ИП */
    const HEALTH_INSURANCE_WITHOUT_PAYOUTS = 'health_insurance_without_payouts';
    /** Взносы на обязательное медицинское страхование */
    const HEALTH_INSURANCE_WITH_PAYOUTS = 'health_insurance_with_payouts';
    /** Взносы на обязательное социальное страхование */
    const HEALTH_INSURANCE = 'health_insurance';
    /** Платеж казино */
    const CASINO = 'casino';
    /** Выдача денежных средств */
    const AGENT_WITHDRAWALS = 'agent_withdrawals';
    /** Подакцизный товар, подлежащий маркировке средством идентификации, не имеющим кода маркировки (в чеке — АТНМ). Пример: алкогольная продукция */
    const NON_MARKED_EXCISE = 'non_marked_excise';
    /** Подакцизный товар, подлежащий маркировке средством идентификации, имеющим код маркировки (в чеке — АТМ). Пример: табак */
    const MARKED_EXCISE = 'marked_excise';
    /** Товар, подлежащий маркировке средством идентификации, имеющим код маркировки, за исключением подакцизного товара (в чеке — ТМ). Пример: обувь, духи, товары легкой промышленности */
    const MARKED = 'marked';
    /** Товар, подлежащий маркировке средством идентификации, не имеющим кода маркировки, за исключением подакцизного товара (в чеке — ТНМ). Пример: меховые изделия */
    const NON_MARKED = 'non_marked';

    protected static $validValues = array(
        self::COMMODITY             => true,
        self::EXCISE                => true,
        self::JOB                   => true,
        self::SERVICE               => true,
        self::GAMBLING_BET          => true,
        self::GAMBLING_PRIZE        => true,
        self::LOTTERY               => true,
        self::LOTTERY_PRIZE         => true,
        self::INTELLECTUAL_ACTIVITY => true,
        self::PAYMENT               => true,
        self::AGENT_COMMISSION      => true,
        self::PROPERTY_RIGHT        => true,
        self::NON_OPERATING_GAIN    => true,
        self::INSURANCE_PREMIUM     => true,
        self::SALES_TAX             => true,
        self::RESORT_FEE            => true,
        self::COMPOSITE             => true,
        self::ANOTHER               => true,

        self::FINE                              => true,
        self::TAX                               => true,
        self::LIEN                              => true,
        self::COST                              => true,
        self::PENSION_INSURANCE_WITHOUT_PAYOUTS => true,
        self::PENSION_INSURANCE_WITH_PAYOUTS    => true,
        self::HEALTH_INSURANCE_WITHOUT_PAYOUTS  => true,
        self::HEALTH_INSURANCE_WITH_PAYOUTS     => true,
        self::HEALTH_INSURANCE                  => true,
        self::CASINO                            => true,
        self::AGENT_WITHDRAWALS                 => true,
        self::NON_MARKED_EXCISE                 => true,
        self::MARKED_EXCISE                     => true,
        self::MARKED                            => true,
        self::NON_MARKED                        => true,
    );
}
