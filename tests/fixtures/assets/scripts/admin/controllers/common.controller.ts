import AWN from 'awesome-notifications';

export class CommonController {
  private modalTemplate: _.CompiledTemplate;

  init(): void {
    this.initAwn();
  }

  finalize(): void {
    // Do nothing.
  }

  private initAwn(): void {
    window.awn = new AWN({
      labels: window.woosync.i18n.awn.awnLabels,
      messages: window.woosync.i18n.awn.awnMessages,
    });
  }
}
