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
        public bool status = false;
        public bool isRun = false;
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
            var httpClient = new HttpClient();

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
            isRun = true;

            foreach (var device in listDevice)
            {
                Task t = new Task(async () =>
                {
                    var a = devices.Where(x => x.Name.Equals(device)).First();

                    int i = 1;

                    while (i <= 40 && isRun)
                    {
                        var screen = KAutoHelper.ADBHelper.ScreenShoot(device);

                        var x = KAutoHelper.CaptureHelper.ResizeImage(screen, 960, 560);

                        var cauhoi = Invert(KAutoHelper.CaptureHelper.CropImage(x, new System.Drawing.Rectangle(181, 86, 400, 32)));
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

                            var httpClient = new HttpClient();

                            var response = await httpClient.GetAsync("http://vannt.click/get?cauhoi=" + cauhoitext + "&A=" + dapanAtext + "&B=" + dapanBtext + "&C=" + dapanCtext + "&D=" + dapanDtext);

                            var data = await response.Content.ReadAsStringAsync();

                            var answer = JsonConvert.DeserializeObject<AnswerResult>(data);

                            if(!answer.Status)
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

                            Task.Delay(1000).Wait();

                            KAutoHelper.ADBHelper.TapByPercent(device, 54.4, 31.4);

                            Task.Delay(2000).Wait();

                        }

                        a.Progress = i + "/40";
                        a.Status = "Đang chạy";

                        dataGridView1.Refresh();

                        Task.Delay(2000).Wait();

                    }

                    a.Name = device;
                    a.Progress = (i - 1) + "/40";
                    a.Status = "Hoàn thành";

                    i = 1;
                    isRun = false;

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

            listDevice = KAutoHelper.ADBHelper.GetDevices();

            var cmd = "adb kill-server && adb start-server";
            KAutoHelper.ADBHelper.ExecuteCMD(cmd.ToString());
            listDevice = KAutoHelper.ADBHelper.GetDevices();

            listDevice.ForEach(x =>
            {
                var device = new Device();
                device.Name = x;
                device.Progress = 0 + "/40";
                device.Status = "Chưa chạy";

                devices.Add(device);
            });
            dataGridView1.AutoGenerateColumns = false;
            dataGridView1.Refresh();

            txtDevice.Text = devices.Count().ToString();

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

        private void lblExpired_Click(object sender, EventArgs e)
        {

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