@php
  use App\Features\SellingTax;
  use App\Models\Tenants\{Profile, Setting, About};
@endphp

<x-filament-panels::page>
  <x-filament::section id="printElement">
    <!-- Selling Details and Member Info remain unchanged -->
  </x-filament::section>
</x-filament-panels::page>

@script()
<script>
  const selling = @js($record);
  const about = @js($about);
  const isTaxEnabled = @js(feature(SellingTax::class));

  console.log('Selling Record:', selling);

  document.getElementById('printInvoice')?.addEventListener('click', () => {
    const printContents = document.getElementById("printElement").innerHTML;
    const originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload();
  });

  document.getElementById('printButton')?.addEventListener('click', async () => {
    const printerData = getPrinter();

    try {
      if (!printerData) {
        new FilamentNotification()
          .title('@lang("Yow! You should choose the printer first in printer setting")')
          .danger()
          .actions([
            new FilamentNotificationAction('Setting')
              .icon('heroicon-o-cog-6-tooth')
              .button()
              .url('/member/printer'),
          ])
          .send();
        return;
      }

      const printer = new Printer(printerData.printerId);
      let printerAction = printer.font?.('a') || printer; // Fallback if font() is missing

      if (about) {
        printerAction.size?.(1)
          .align?.('center')
          .text(about.shop_name ?? '')
          .size?.(0)
          .text(about.shop_location ?? '');

        if (printerData.header) {
          printerAction.text(printerData.header);
        }

        printerAction.align?.('left').text('-------------------------------');
      }

      if (selling.user?.name) {
        printerAction.table(['@lang("Cashier")', selling.user.name]);
      }

      if (selling.table?.number) {
        printerAction.table(['@lang("Table")', selling.table.number]);
      }

      if (selling.payment_method?.name) {
        printerAction.table(['@lang("Payment method")', selling.payment_method.name]);
      }

      if (selling.member?.name) {
        printerAction.table(['@lang("Member")', selling.member.name]);
      }

      printerAction.text('-------------------------------');

      selling.selling_details?.forEach(sellingDetail => {
        const pricePerItem = sellingDetail.price / sellingDetail.qty;
        let price = sellingDetail.price;

        printerAction.table([
          sellingDetail.product?.name ?? 'Unknown',
          `${moneyFormat(pricePerItem)} x ${sellingDetail.qty}`
        ]);

        if (sellingDetail.discount_price > 0) {
          price -= sellingDetail.discount_price;
          printerAction
            .align?.('right')
            .text(`(${moneyFormat(sellingDetail.discount_price)})`);
        }

        printerAction
          .align?.('right')
          .text(moneyFormat(price))
          .align?.('left');
      });

      printerAction.text('-------------------------------');

      if (isTaxEnabled) {
        printerAction
          .table(['@lang("Tax")', `${selling.tax ?? 0}%`])
          .table(['@lang("Tax price")', moneyFormat(selling.tax_price ?? 0)]);
      }

      printerAction
        .table(['@lang("Subtotal")', moneyFormat(selling.total_price ?? 0)])
        .table(['@lang("Discount")', `(${moneyFormat((selling.total_discount_per_item ?? 0) + (selling.discount_price ?? 0))})`])
        .table(['@lang("Total price")', moneyFormat(selling.grand_total_price ?? 0)])
        .text('-------------------------------')
        .table(['@lang("Payed money")', moneyFormat(selling.payed_money ?? 0)])
        .table(['@lang("Money changes")', moneyFormat(selling.money_changes ?? 0)]);

      if (printerData.footer) {
        printerAction
          .text('-------------------------------')
          .text(printerData.footer);
      }

      // Fix: .feed() removed, fallback to newline
      printerAction.text('\n\n').cut?.().print?.();

    } catch (error) {
      console.error('Print Error:', error);
      new FilamentNotification()
        .title('Printing error')
        .body(error.message || 'Unknown printing error')
        .danger()
        .send();
    }
  });

  function moneyFormat(value) {
    const currency = '{{ Setting::get("currency", "KSH") }}';
    return new Intl.NumberFormat('en-KE', {
      style: 'currency',
      currency: currency
    }).format(value);
  }
</script>
@endscript
