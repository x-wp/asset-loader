import AWN from 'awesome-notifications';

declare type SdlResponse = {
  success: boolean;
  message: string;
};

declare type jQxhr = JQuery.jqXHR<SdlResponse>;

export class EditProductController {
  private metabox: HTMLTableElement;
  private inputs: NodeListOf<HTMLInputElement>;

  private awn: AWN;

  init(): void {
    this.metabox = document.querySelector('#woosync-product-sdl-data table');
    this.inputs =
      this?.metabox?.querySelectorAll<HTMLInputElement>('.sdl-input');
  }
  finalize(): void {
    if (!this.metabox) {
      return;
    }

    this.initAwn();
    this.maybeReadonly();

    this.metabox
      .querySelector('.save-sdl-data')
      .addEventListener('click', (e) => this.saveSdlData(e));
  }

  private initAwn(): void {
    this.awn = new AWN<jQxhr>({
      formatError: (e) => e.responseJSON.message,
    });
  }

  private maybeReadonly(): void {
    if (this.metabox.dataset.admin == 'yes') {
      return;
    }

    this.inputs.forEach((input) => jQuery(input).prop('readonly', true));
  }

  private saveSdlData(e: Event): void {
    e.preventDefault();

    const url = this.metabox.dataset.url;
    const data = {
      action: this.metabox.dataset.action,
      security: this.metabox.dataset.nonce,
      sdl: {},
    };

    this.inputs.forEach((input) => {
      data.sdl[input.id] = input.value;
    });

    this.awn.asyncBlock(
      jQuery.ajax({
        url,
        type: 'POST',
        data,
      }) as unknown as Promise<SdlResponse>,
      (r) => this.awn.success(r.message),
    );
  }
}
