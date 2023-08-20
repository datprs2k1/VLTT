using Patagames.Ocr.Enums;
using Patagames.Ocr;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Net.Http;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Drawing;
using Newtonsoft.Json;

namespace HoaDang
{

    public partial class Main : Form
    {
        public List<string> listDevice = new List<string>();
        public List<Device> devices = new List<Device>();
        public List<string> selects = new List<string>();
        public bool status = false;
        public bool isRun = false;
        public HttpClient httpClient = new HttpClient();
        public Main()
        {
            InitializeComponent();

            getInfo();

            listDevice = KAutoHelper.ADBHelper.GetDevices();

            listDevice.ForEach(x =>
            {
                var device = new Device();
                device.Name = x;
                device.Progress = 0 + "/40";
                device.Status = "Chưa chạy";

                devices.Add(device);
            });

            Control.CheckForIllegalCrossThreadCalls = false;


            dataGridView1.DataSource = devices;
            dataGridView1.AutoGenerateColumns = false;
            dataGridView1.Refresh();

            txtDevice.Text = listDevice.Count.ToString();

        }

        public async void getInfo()
        {
            var response = await httpClient.GetAsync("http://vannt.click/getinfo");

            var answer = await response.Content.ReadAsStringAsync();

            var info = JsonConvert.DeserializeObject<InfoResult>(answer);

            if (!info.Status)
            {
                MessageBox.Show(info.Message, "Lỗi");
                Application.Exit();
            }
            else
            {
                lblName.Text = "Xin chào " + info.Name;
                lblExpired.Text = info.Expired;
                lblStatus.Text = info.Message;
                lblTotal.Text = info.Total;
            }

        }

        private void btnList1_Click(object sender, EventArgs e)
        {
            selects.Clear();

            foreach(DataGridViewRow row in dataGridView1.Rows)
            {
                if (row.Cells[0].Value != null && row.Cells[0].Value.ToString().Equals("True"))
                {
                    selects.Add(row.Cells[1].Value.ToString());
                }
            }


            var list = selects.ToList().Count > 0 ? selects : listDevice;


            isRun = true;

            foreach (var device in list)
            {
                Task t = new Task(async () =>
                {
                    var a = devices.Where(x => x.Name.Equals(device)).First();

                    int i = 1;

                    bool run = true;

                    KAutoHelper.ADBHelper.TapByPercent(device, 3.2, 33.2);

                    Task.Delay(5000).Wait();

                    while (i <= 40 && isRun && run)
                    {
                        var screen = KAutoHelper.ADBHelper.ScreenShoot(device);

                        var x = KAutoHelper.CaptureHelper.ResizeImage(screen, 960, 560);

                        var cauhoi = Invert(KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(181, 86, 400, 24)));
                        var dapanA = Invert(KAutoHelper.CaptureHelper.ScaleImage(KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(192, 262, 168, 32)), 1.05));
                        var dapanB = Invert(KAutoHelper.CaptureHelper.ScaleImage(KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(392, 262, 168, 32)), 1.05));
                        var dapanC = Invert(KAutoHelper.CaptureHelper.ScaleImage(KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(592, 262, 168, 32)), 1.05));
                        var dapanD = Invert(KAutoHelper.CaptureHelper.ScaleImage(KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(192, 302, 168, 32)), 1.05));

                        using (var api = OcrApi.Create())
                        {
                            Languages[] langs = { Languages.Vietnamese };

                            api.Init(Languages.Vietnamese   );

                            string cauhoitext = api.GetTextFromImage(cauhoi) ?? "";
                            string dapanAtext = api.GetTextFromImage(dapanA) ?? "";
                            string dapanBtext = api.GetTextFromImage(dapanB) ?? "";
                            string dapanCtext = api.GetTextFromImage(dapanC) ?? "";
                            string dapanDtext = api.GetTextFromImage(dapanD) ?? "";

                            IEnumerable<KeyValuePair<string, string>> queries = new List<KeyValuePair<string, string>>()
                            {
                                new KeyValuePair<string, string>("cauhoi", cauhoitext),
                                new KeyValuePair<string, string>("A", dapanAtext),
                                new KeyValuePair<string, string>("B", dapanBtext),
                                new KeyValuePair<string, string>("C", dapanCtext),
                                new KeyValuePair<string, string>("D", dapanDtext)
                            };

                            HttpContent q = new FormUrlEncodedContent(queries);

                            var response = await httpClient.PostAsync("http://vannt.click/get", q);

                            var data = await response.Content.ReadAsStringAsync();

                            var answer = JsonConvert.DeserializeObject<AnswerResult>(data);

                            if (!answer.Status)
                            {
                                MessageBox.Show(answer.Message, "Lỗi");

                                Application.Exit();

                            }
                            else if (answer.Message.Equals("1"))
                            {
                                KAutoHelper.ADBHelper.TapByPercent(device, 30.5, 49.0);
                            }
                            else if (answer.Message.Equals("2"))
                            {
                                KAutoHelper.ADBHelper.TapByPercent(device, 49.0, 49.0);
                            }
                            else if (answer.Message.Equals("3"))
                            {
                                KAutoHelper.ADBHelper.TapByPercent(device, 70.6, 49);
                            }
                            else
                            {
                                KAutoHelper.ADBHelper.TapByPercent(device, 28.4, 56.5);
                            }

                            i++;

                            Task.Delay(500).Wait();

                            KAutoHelper.ADBHelper.TapByPercent(device, 54.4, 31.4);

                            Task.Delay(500).Wait();

                        }

                        a.Progress = i + "/40";
                        a.Status = "Đang chạy";

                        dataGridView1.Refresh();

                    }

                    a.Name = device;
                    a.Progress = (i - 1) + "/40";
                    a.Status = "Hoàn thành";

                    run = false;

                    dataGridView1.Refresh();
                    return;

                });

                t.Start();
            }
        }



        private void button5_Click(object sender, EventArgs e)
        {
            listDevice.Clear();
            devices.Clear();

            var cmd = "adb kill-server && adb start-server";
            KAutoHelper.ADBHelper.ExecuteCMD(cmd.ToString());

            Application.Restart();

        }

        public Bitmap Invert(Bitmap image)
        {
            for (int y = 0; (y <= (image.Height - 1)); y++)
            {
                for (int x = 0; (x <= (image.Width - 1)); x++)
                {
                    Color inv = image.GetPixel(x, y);
                    inv = Color.FromArgb(inv.A, (255 - inv.R), (255 - inv.G), (255 - inv.B));
                    image.SetPixel(x, y, inv);
                }
            }

            return image;
        }

        private void button2_Click(object sender, EventArgs e)
        {
            isRun = false;
        }

    }

    public class Device
    {
        public string Name { get; set; }
        public string Progress { get; set; }
        public string Status { get; set; }
    }

    public class InfoResult
    {
        public string Message { get; set; }
        public bool Status { get; set; }
        public string Name { get; set; }
        public string Expired { get; set; }
        public string Total { get; set; }
    }


    public class AnswerResult
    {
        public string Message { get; set; }
        public bool Status { get; set; }
    }

}