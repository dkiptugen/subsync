<?php

	namespace App\Http\Services;

	use App\Models\EmailTemplate;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Mail;

	class EmailService
		{
			public function __construct ($product, $type)
				{
					$this->product = $product;
					$this->type    = $type;
				}

			public function sendTemplateEmail ($recipientEmail, $data = [])
				{
					try
						{
							if (!is_null ($this->product))
								{
									$template = EmailTemplate::whereJsonContains ('products',
									                                              $this->product)->where ('type',
									                                                                      $this->type)->first ()
									;
								}
							else
								{
									$template = EmailTemplate::where ('type', $this->type)->first ()
									;
								}
							if (!is_null ($template))
								{
									$subject = $template->subject;
									$body    = $this->replacePlaceholders ($template->body, $data);
								}
							else
								{
									$templ   = config ('email_sample.'.$this->type);
									$subject = $templ->subject;
									$body    = $this->replacePlaceholders ($templ->body, $data);
								}
							Mail::to ($recipientEmail)->send (new \App\Mail\CustomTemplateEmail($subject, $body));
						}
					catch (\Exception $e)
						{
							Log::error ($e->getMessage ());
						}

				}

			private function replacePlaceholders ($body, $data)
				{
					$params = config ('email.'.$this->type);
					foreach ($params as $param)
						{
							$body = str_replace ('{{ '.$param.' }}', $data[$param] ?? '', $body);
						}

					return $body;
				}
		}
